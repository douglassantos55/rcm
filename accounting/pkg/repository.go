package pkg

import (
	"database/sql"
	"errors"
	"time"

	"github.com/google/uuid"
	_ "github.com/mattn/go-sqlite3"
)

type entry struct {
	Id      uuid.UUID
	Value   float64
	Account Account
	TransId uuid.UUID
	Date    int64
	PayDate *int64
}

type Repository interface {
	Create(entry *Entry) (*Entry, error)
	Update(entry *Entry) (*Entry, error)
	Delete(id uuid.UUID) error
	FindByTransaction(id uuid.UUID) (*Entry, error)
}

type SqlRepository struct {
	connection *sql.DB
}

func NewSqlRepository() (Repository, error) {
	conn, err := sql.Open("sqlite3", "test.db")
	if err != nil {
		return nil, err
	}

	return &SqlRepository{
		connection: conn,
	}, nil
}

func (r *SqlRepository) Create(entry *Entry) (*Entry, error) {
	stmt, err := r.connection.Prepare(`
        INSERT INTO entries (id, value, date, pay_date, account, trans_id)
        VALUES (?, ?, ?, ?, ?, ?)
    `)

	if err != nil {
		return nil, err
	}

	var id uuid.UUID
	if entry.Id == uuid.Nil {
		id = uuid.New()
	} else {
		id = entry.Id
	}

	if _, err = stmt.Exec(
		id,
		entry.Value,
		entry.Date,
		entry.PayDate,
		entry.Account,
		entry.TransId,
	); err != nil {
		return nil, err
	}

	return &Entry{
		Id:      id,
		Value:   entry.Value,
		Date:    entry.Date,
		PayDate: entry.PayDate,
		Account: entry.Account,
		TransId: entry.TransId,
	}, nil
}

func (r *SqlRepository) Update(entry *Entry) (*Entry, error) {
	if entry.Id == uuid.Nil {
		return nil, errors.New("the given entry has no ID")
	}

	stmt, err := r.connection.Prepare(`
        UPDATE entries
        SET value = ?, date = ?, pay_date = ?, account = ?, trans_id = ?
        WHERE id = ?
    `)

	if err != nil {
		return nil, err
	}

	result, err := stmt.Exec(
		entry.Value,
		entry.Date,
		entry.PayDate,
		entry.Account,
		entry.TransId,
		entry.Id,
	)

	if err != nil {
		return nil, err
	}

	updated, err := result.RowsAffected()
	if err != nil {
		return nil, err
	}

	if updated == 0 {
		return nil, errors.New("could not update entry")
	}

	return entry, nil
}

func (r *SqlRepository) Delete(id uuid.UUID) error {
	stmt, err := r.connection.Prepare("DELETE FROM entries WHERE id = ?")
	if err != nil {
		return err
	}

	result, err := stmt.Exec(id)
	if err != nil {
		return err
	}

	deleted, err := result.RowsAffected()
	if err != nil {
		return err
	}

	if deleted == 0 {
		return errors.New("could not delete entry")
	}

	return nil
}

func (r *SqlRepository) FindByTransaction(id uuid.UUID) (*Entry, error) {
	stmt, err := r.connection.Prepare(`
        SELECT id, value, account, trans_id, unixepoch(date), unixepoch(pay_date)
        FROM entries
        WHERE trans_id = ?
    `)

	if err != nil {
		return nil, err
	}

	var entry entry
	row := stmt.QueryRow(id)

	if err := row.Scan(
		&entry.Id,
		&entry.Value,
		&entry.Account,
		&entry.TransId,
		&entry.Date,
		&entry.PayDate,
	); err != nil {
		return nil, err
	}

	date := time.UnixMilli(entry.Date * 1000)
	payDate := time.UnixMilli(*entry.PayDate * 1000)

	return &Entry{
		Id:      entry.Id,
		Value:   entry.Value,
		Account: entry.Account,
		TransId: entry.TransId,
		Date:    date,
		PayDate: &payDate,
	}, nil
}
