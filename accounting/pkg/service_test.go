package pkg_test

import (
	"fmt"
	"testing"
	"time"

	"github.com/google/uuid"
	"reconcip.com.br/accounting/pkg"
)

type InMemoryRepository struct {
	entries map[uuid.UUID]*pkg.Entry
}

func (r *InMemoryRepository) Create(entry *pkg.Entry) (*pkg.Entry, error) {
	r.entries[entry.Id] = entry
	return entry, nil
}

func (r *InMemoryRepository) Update(entry *pkg.Entry) (*pkg.Entry, error) {
	r.entries[entry.Id] = entry
	return entry, nil
}

func (r *InMemoryRepository) FindByTransaction(id uuid.UUID) (*pkg.Entry, error) {
	for _, entry := range r.entries {
		if entry.TransId == id {
			return entry, nil
		}
	}
	return nil, fmt.Errorf("entry not found for transaction: %v", id)
}

func NewInMemoryRepository() pkg.Repository {
	return &InMemoryRepository{
		entries: make(map[uuid.UUID]*pkg.Entry),
	}
}

func TestService(t *testing.T) {
	t.Run("create inflow entry without date", func(t *testing.T) {
		svc := pkg.NewService(NewInMemoryRepository())

		id := uuid.New()
		entry, err := svc.CreateInflow(pkg.Transaction{
			Id:    id,
			Value: 500.0,
		})

		if err != nil {
			t.Fatal(err)
		}

		if entry.Value != 500.0 {
			t.Errorf("expected value %v, got %v", 500.0, entry.Value)
		}

		if entry.Type != pkg.Inflow {
			t.Errorf("expected type %v, got %v", pkg.Inflow, entry.Type)
		}

		if entry.TransId != id {
			t.Errorf("expected id %v, got %v", id, entry.TransId)
		}

		diff := time.Since(entry.Date)
		if diff.Seconds() > 1 {
			t.Errorf("expected date %v, got %v", time.Now(), entry.Date)
		}

		if entry.PayDate != nil {
			t.Errorf("should not be paid, got %v", entry.PayDate)
		}
	})

	t.Run("create inflow entry with date", func(t *testing.T) {
		svc := pkg.NewService(NewInMemoryRepository())

		date := time.Now().AddDate(0, 1, 0)
		entry, err := svc.CreateInflow(pkg.Transaction{
			Id:    uuid.New(),
			Value: 500.0,
			Date:  date,
		})

		if err != nil {
			t.Fatal(err)
		}

		if entry.Value != 500.0 {
			t.Errorf("expected value %v, got %v", 500.0, entry.Value)
		}

		if entry.Type != pkg.Inflow {
			t.Errorf("expected type %v, got %v", pkg.Inflow, entry.Type)
		}

		if !entry.Date.Equal(date) {
			t.Errorf("expected %v, got %v", date, entry.Date)
		}

		if entry.PayDate != nil {
			t.Errorf("should not be paid, got %v", entry.PayDate)
		}
	})

	t.Run("create inflow entry with payment date", func(t *testing.T) {
		svc := pkg.NewService(NewInMemoryRepository())

		date := time.Now().AddDate(0, 0, -1)
		paymentDate := time.Now().AddDate(0, 1, 0)

		entry, err := svc.CreateInflow(pkg.Transaction{
			Id:      uuid.New(),
			Value:   500.0,
			Date:    date,
			PayDate: &paymentDate,
		})

		if err != nil {
			t.Fatal(err)
		}

		if entry.Value != 500.0 {
			t.Errorf("expected value %v, got %v", 500.0, entry.Value)
		}

		if entry.Type != pkg.Inflow {
			t.Errorf("expected type %v, got %v", pkg.Inflow, entry.Type)
		}

		if !entry.Date.Equal(date) {
			t.Errorf("expected %v, got %v", date, entry.Date)
		}

		if !entry.PayDate.Equal(paymentDate) {
			t.Errorf("should be paid, expected %v, got %v", paymentDate, entry.PayDate)
		}
	})

	t.Run("create outflow entry without date", func(t *testing.T) {
		svc := pkg.NewService(NewInMemoryRepository())

		entry, err := svc.CreateOutflow(pkg.Transaction{
			Id:    uuid.New(),
			Value: 100.53,
		})

		if err != nil {
			t.Fatal(err)
		}

		if entry.Type != pkg.Outflow {
			t.Errorf("expected type %v, got %v", pkg.Outflow, entry.Type)
		}

		if entry.Value != 100.53 {
			t.Errorf("expected value %v, got %v", 100.53, entry.Value)
		}

		diff := time.Since(entry.Date)
		if diff.Seconds() > 1 {
			t.Errorf("expected date %v, got %v", time.Now(), entry.Date)
		}

		if entry.PayDate != nil {
			t.Errorf("should not be paid, got %v", entry.PayDate)
		}
	})

	t.Run("create outflow entry with date", func(t *testing.T) {
		svc := pkg.NewService(NewInMemoryRepository())

		date := time.Now().AddDate(0, 0, -5)
		entry, err := svc.CreateOutflow(pkg.Transaction{
			Id:    uuid.New(),
			Value: 100.53,
			Date:  date,
		})

		if err != nil {
			t.Fatal(err)
		}

		if entry.Type != pkg.Outflow {
			t.Errorf("expected type %v, got %v", pkg.Outflow, entry.Type)
		}

		if entry.Value != 100.53 {
			t.Errorf("expected value %v, got %v", 100.53, entry.Value)
		}

		if !entry.Date.Equal(date) {
			t.Errorf("expected date %v, got %v", date, entry.Date)
		}

		if entry.PayDate != nil {
			t.Errorf("should not be paid, got %v", entry.PayDate)
		}
	})

	t.Run("create outflow entry with payment date", func(t *testing.T) {
		svc := pkg.NewService(NewInMemoryRepository())

		date := time.Now().AddDate(0, 0, -5)
		payDate := time.Now().AddDate(0, 0, -1)

		entry, err := svc.CreateOutflow(pkg.Transaction{
			Id:      uuid.New(),
			Value:   100.53,
			Date:    date,
			PayDate: &payDate,
		})

		if err != nil {
			t.Fatal(err)
		}

		if entry.Type != pkg.Outflow {
			t.Errorf("expected type %v, got %v", pkg.Outflow, entry.Type)
		}

		if entry.Value != 100.53 {
			t.Errorf("expected value %v, got %v", 100.53, entry.Value)
		}

		if !entry.Date.Equal(date) {
			t.Errorf("expected date %v, got %v", date, entry.Date)
		}

		if !entry.PayDate.Equal(payDate) {
			t.Errorf("expected payment date %v, got %v", payDate, entry.PayDate)
		}
	})

	t.Run("update existing entry", func(t *testing.T) {
		svc := pkg.NewService(NewInMemoryRepository())

		transactionId := uuid.New()

		_, err := svc.CreateInflow(pkg.Transaction{
			Id:    transactionId,
			Value: 100.53,
		})

		if err != nil {
			t.Fatal(err)
		}

		date := time.Now().AddDate(0, 0, -7)
		payDate := time.Now().AddDate(0, 0, -2)

		entry, err := svc.UpdateEntry(pkg.Transaction{
			Id:      transactionId,
			Value:   200.53,
			Date:    date,
			PayDate: &payDate,
		})

		if err != nil {
			t.Fatal(err)
		}

		if entry.Type != pkg.Inflow {
			t.Errorf("expected type %v, got %v", pkg.Inflow, entry.Type)
		}

		if entry.Value != 200.53 {
			t.Errorf("expected value %v, got %v", 200.53, entry.Value)
		}

		if entry.Date.IsZero() {
			t.Errorf("expected date %v, got %v", date, entry.Date)
		}

		if entry.PayDate == nil {
			t.Errorf("expected payment date %v, got %v", payDate, entry.PayDate)
		}
	})

	t.Run("update non existing entry", func(t *testing.T) {
		svc := pkg.NewService(NewInMemoryRepository())

		_, err := svc.UpdateEntry(pkg.Transaction{
			Id:    uuid.New(),
			Value: 5000,
		})

		if err == nil {
			t.Fatal("expected error")
		}
	})
}
