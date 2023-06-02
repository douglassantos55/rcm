package pkg_test

import (
	"testing"
	"time"

	"github.com/google/uuid"
	"reconcip.com.br/accounting/pkg"
)

func TestRepository(t *testing.T) {
	t.Run("create", func(t *testing.T) {
		repository, err := pkg.NewSqlRepository()

		if err != nil {
			t.Fatal(err)
		}

		entry, err := repository.Create(&pkg.Entry{
			Date:    time.Now(),
			Account: pkg.OtherRevenue,
			Value:   5005.53,
			TransId: uuid.New(),
		})

		if err != nil {
			t.Fatal(err)
		}

		if entry == nil {
			t.Fatal("should have created entry")
		}

		if entry.Id.String() == "" {
			t.Error("should have an ID")
		}
	})

	t.Run("create with id", func(t *testing.T) {
		repository, err := pkg.NewSqlRepository()

		if err != nil {
			t.Fatal(err)
		}

		id := uuid.New()

		entry, err := repository.Create(&pkg.Entry{
			Id:      id,
			Date:    time.Now(),
			Account: pkg.OtherRevenue,
			Value:   5005.53,
			TransId: uuid.New(),
		})

		if err != nil {
			t.Fatal(err)
		}

		if entry == nil {
			t.Fatal("should have created entry")
		}

		if entry.Id != id {
			t.Errorf("expected ID %v, got %v", id, entry.Id)
		}
	})

	t.Run("update without ID", func(t *testing.T) {
		repository, err := pkg.NewSqlRepository()
		if err != nil {
			t.Fatal(err)
		}

		_, err = repository.Update(&pkg.Entry{
			Date:    time.Now(),
			Account: pkg.OtherRevenue,
			Value:   5005.53,
			TransId: uuid.New(),
		})

		if err == nil {
			t.Error("should not update without ID")
		}
	})

	t.Run("update non existing", func(t *testing.T) {
		repository, err := pkg.NewSqlRepository()

		if err != nil {
			t.Fatal(err)
		}

		_, err = repository.Update(&pkg.Entry{
			Id:      uuid.New(),
			Date:    time.Now(),
			Account: pkg.OtherRevenue,
			Value:   5005.53,
			TransId: uuid.New(),
		})

		if err == nil {
			t.Error("should not update non existing entry")
		}
	})

	t.Run("update existing", func(t *testing.T) {
		repository, err := pkg.NewSqlRepository()
		if err != nil {
			t.Fatal(err)
		}

		entry, err := repository.Create(&pkg.Entry{
			Id:      uuid.New(),
			Date:    time.Now(),
			Account: pkg.OtherRevenue,
			Value:   5005.53,
			TransId: uuid.New(),
		})

		now := time.Now()
		entry.PayDate = &now

		entry.Value = 1005.22
		entry.Date = time.Now().AddDate(0, 0, -5)
		entry.Account = pkg.RentRevenue

		updated, err := repository.Update(entry)

		if err != nil {
			t.Fatal(err)
		}

		if *updated.PayDate != now {
			t.Errorf("expected payment date %v, got %v", now, updated.PayDate)
		}

		if updated.Value != 1005.22 {
			t.Errorf("expected value %v, got %v", 1005.22, updated.Value)
		}

		if updated.Account != pkg.RentRevenue {
			t.Errorf("expected account %v, got %v", pkg.RentRevenue, updated.Account)
		}
	})

	t.Run("delete non existing", func(t *testing.T) {
		repository, err := pkg.NewSqlRepository()
		if err != nil {
			t.Fatal(err)
		}

		if err := repository.Delete(uuid.New()); err == nil {
			t.Error("should not delete non existing entry")
		}
	})

	t.Run("delete existing", func(t *testing.T) {
		repository, err := pkg.NewSqlRepository()
		if err != nil {
			t.Fatal(err)
		}

		entry, err := repository.Create(&pkg.Entry{
			Value:   100,
			Date:    time.Now(),
			Account: pkg.RentRevenue,
			TransId: uuid.New(),
		})

		if err != nil {
			t.Fatal(err)
		}

		if err := repository.Delete(entry.Id); err != nil {
			t.Fatal(err)
		}
	})
}
