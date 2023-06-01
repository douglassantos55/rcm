package pkg

import (
	"time"

	"github.com/google/uuid"
)

type Account int

const (
	RentRevenue Account = iota
	OtherRevenue
	DeliveryExpense
)

type Entry struct {
	Id      uuid.UUID  `json:"id"`
	Value   float64    `json:"value"`
	Date    time.Time  `json:"date"`
	PayDate *time.Time `json:"pay_date"`
	Account Account    `json:"account"`
	TransId uuid.UUID  `json:"transaction_id"`
}

type Transaction struct {
	Id      uuid.UUID  `json:"id"`
	Date    time.Time  `json:"date"`
	PayDate *time.Time `json:"pay_date"`
	Value   float64    `json:"value"`
}

type Service interface {
	RentCreated(transaction Transaction) (*Entry, error)
	UpdateEntry(transaction Transaction) (*Entry, error)
	DeleteEntry(transactionId uuid.UUID) error
}

type Repository interface {
	Create(entry *Entry) (*Entry, error)
	Update(entry *Entry) (*Entry, error)
	Delete(id uuid.UUID) error
	FindByTransaction(id uuid.UUID) (*Entry, error)
}

type service struct {
	repo Repository
}

func NewService(repo Repository) Service {
	return &service{repo}
}

func (s *service) RentCreated(transaction Transaction) (*Entry, error) {
	entry := &Entry{
		Id:      uuid.New(),
		Date:    transaction.Date,
		TransId: transaction.Id,
		PayDate: transaction.PayDate,
		Value:   transaction.Value,
		Account: RentRevenue,
	}

	if transaction.Date.IsZero() {
		entry.Date = time.Now()
	}

	return s.repo.Create(entry)
}

func (s *service) UpdateEntry(transaction Transaction) (*Entry, error) {
	entry, err := s.repo.FindByTransaction(transaction.Id)
	if err != nil {
		return nil, err
	}

	entry.Value = transaction.Value
	entry.Date = transaction.Date
	entry.PayDate = transaction.PayDate

	return s.repo.Update(entry)
}

func (s *service) DeleteEntry(transactionId uuid.UUID) error {
	entry, err := s.repo.FindByTransaction(transactionId)
	if err != nil {
		return err
	}
	return s.repo.Delete(entry.Id)
}
