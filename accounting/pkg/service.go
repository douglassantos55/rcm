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
	Paid    bool       `json:"paid"`
	Date    time.Time  `json:"date"`
	PayDate *time.Time `json:"pay_date"`
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
		PayDate: transaction.PayDate,
		Value:   transaction.Value,
	}

	if transaction.Date.IsZero() {
		entry.Date = time.Now()
	}

	return s.repo.Create(entry)
}

func (s *service) UpdateEntry(transaction Transaction) (*Entry, error) {
	return nil, nil
}

func (s *service) DeleteEntry(transaction Transaction) (*Entry, error) {
	return nil, nil
}
