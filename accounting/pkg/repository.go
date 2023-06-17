package pkg

import (
	"database/sql"
	"errors"
	"fmt"
	"time"

	"github.com/doug-martin/goqu/v9"
	_ "github.com/doug-martin/goqu/v9/dialect/mysql"
	_ "github.com/doug-martin/goqu/v9/dialect/sqlite3"
	_ "github.com/go-sql-driver/mysql"
	"github.com/google/uuid"
	_ "github.com/mattn/go-sqlite3"
)

var ErrEntryNotFound = errors.New("Entry not found")

type Repository interface {
	Create(entry *Entry) (*Entry, error)
	Update(entry *Entry) (*Entry, error)
	Delete(id uuid.UUID) error
	FindByTransaction(id uuid.UUID) (*Entry, error)
}

type SqlRepository struct {
	connection *goqu.Database
}

type Driver interface {
	Name() string
	DSN() string
}

type MySQL struct {
	Host     string
	User     string
	Password string
	Database string
}

func (driver MySQL) Name() string {
	return "mysql"
}

func (driver MySQL) DSN() string {
	return fmt.Sprintf(
		"%s:%s@tcp(%s)/%s?parseTime=true",
		driver.User,
		driver.Password,
		driver.Host,
		driver.Database,
	)
}

type Sqlite struct {
	Filename string
}

func (driver Sqlite) Name() string {
	return "sqlite3"
}

func (driver Sqlite) DSN() string {
	return "file:" + driver.Filename + "?_loc=auto"
}

func NewSqlRepository(driver Driver) (Repository, error) {
	conn, err := sql.Open(driver.Name(), driver.DSN())
	if err != nil {
		return nil, err
	}

	return &SqlRepository{
		connection: goqu.New(driver.Name(), conn),
	}, nil
}

func (r *SqlRepository) Create(entry *Entry) (*Entry, error) {
	if entry.Id == uuid.Nil {
		entry.Id = uuid.New()
	}

	query := r.connection.Insert("entries").Rows(entry).Executor()
	if _, err := query.Exec(); err != nil {
		return nil, err
	}

	return entry, nil
}

func (r *SqlRepository) Update(entry *Entry) (*Entry, error) {
	if entry.Id == uuid.Nil {
		return nil, errors.New("the given entry has no ID")
	}

	query := r.connection.Update("entries").Set(entry).Where(goqu.Ex{"id": entry.Id}).Executor()

	result, err := query.Exec()
	if err != nil {
		return nil, err
	}

	_, err = result.RowsAffected()
	if err != nil {
		return nil, err
	}

	return entry, nil
}

func (r *SqlRepository) Delete(id uuid.UUID) error {
	query := r.connection.Delete("entries").Where(goqu.Ex{"id": id}).Executor()

	result, err := query.Exec()
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
	query := r.connection.From("entries").Where(goqu.Ex{"trans_id": id})

	if r.connection.Dialect() == "sqlite3" {
		return scanSqliteWithDate(query)
	}

	var entry Entry
	found, err := query.ScanStruct(&entry)

	if err != nil {
		return nil, err
	}

	if !found {
		return nil, ErrEntryNotFound
	}

	return &entry, nil
}

func scanSqliteWithDate(query *goqu.SelectDataset) (*Entry, error) {
	var entry struct {
		Id      uuid.UUID `db:"id" goqu:"skipupdate"`
		Value   float64   `db:"value"`
		Date    string    `db:"date"`
		PayDate *string   `db:"pay_date"`
		Account Account   `db:"account"`
		TransId uuid.UUID `db:"trans_id"`
	}

	found, err := query.ScanStruct(&entry)
	if err != nil {
		return nil, err
	}

	if !found {
		return nil, ErrEntryNotFound
	}

	date, err := time.Parse(time.RFC3339, entry.Date)
	if err != nil {
		return nil, err
	}

	payDate, err := time.Parse(time.RFC3339, *entry.PayDate)
	if err != nil {
		return nil, err
	}

	return &Entry{
		Id:      entry.Id,
		Value:   entry.Value,
		Date:    date,
		PayDate: &payDate,
		Account: entry.Account,
		TransId: entry.TransId,
	}, nil
}
