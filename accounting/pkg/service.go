package pkg

import (
	"time"

	"github.com/google/uuid"
)

type EntryType int

const (
	Inflow EntryType = iota
	Outflow
)

type Entry struct {
	Id      uuid.UUID  `json:"id"`
	Type    EntryType  `json:"type"`
	Value   float64    `json:"value"`
	Date    time.Time  `json:"date"`
	PayDate *time.Time `json:"pay_date"`
	TransId uuid.UUID  `json:"transaction_id"`
}

type Transaction struct {
	Id      uuid.UUID  `json:"id"`
	Date    time.Time  `json:"date"`
	PayDate *time.Time `json:"pay_date"`
	Value   float64    `json:"value"`
}

type Service interface {
	CreateInflow(transaction Transaction) (*Entry, error)
	CreateOutflow(transaction Transaction) (*Entry, error)
	UpdateEntry(transaction Transaction) (*Entry, error)
	DeleteEntry(transaction Transaction) (*Entry, error)
}

type Repository interface {
	Create(entry *Entry) (*Entry, error)
	Update(entry *Entry) (*Entry, error)
	FindByTransaction(id uuid.UUID) (*Entry, error)
}

type service struct {
	repo Repository
}

func NewService(repo Repository) Service {
	return &service{repo}
}

func (s *service) CreateInflow(transaction Transaction) (*Entry, error) {
	return s.createEntry(transaction, Inflow)
}

func (s *service) CreateOutflow(transaction Transaction) (*Entry, error) {
	return s.createEntry(transaction, Outflow)
}

func (s *service) createEntry(transaction Transaction, entryType EntryType) (*Entry, error) {
	entry := &Entry{
		Id:      uuid.New(),
		Date:    transaction.Date,
		Type:    entryType,
		TransId: transaction.Id,
		PayDate: transaction.PayDate,
		Value:   transaction.Value,
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

func (s *service) DeleteEntry(transaction Transaction) (*Entry, error) {
	return nil, nil
}
