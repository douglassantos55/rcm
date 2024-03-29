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
	Id      uuid.UUID  `json:"id" db:"id" goqu:"skipupdate"`
	Value   float64    `json:"value" db:"value"`
	Date    time.Time  `json:"date" db:"date"`
	PayDate *time.Time `json:"pay_date" db:"pay_date"`
	Account Account    `json:"account" db:"account"`
	TransId uuid.UUID  `json:"transaction_id" db:"trans_id"`
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

type service struct {
	repo Repository
}

func NewService(repo Repository) Service {
	return &service{repo}
}

func (s *service) RentCreated(transaction Transaction) (*Entry, error) {
	entry := &Entry{
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
