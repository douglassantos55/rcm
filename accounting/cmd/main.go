package main

import (
	"log"
	"os"

	"reconcip.com.br/accounting/pkg"
)

func main() {
	driver := pkg.MySQL{
		Host:     os.Getenv("DB_HOST"),
		User:     os.Getenv("DB_USERNAME"),
		Password: os.Getenv("DB_PASSWORD"),
		Database: os.Getenv("DB_DATABASE"),
	}

	repository, err := pkg.NewSqlRepository(driver)
	if err != nil {
		log.Fatal(err)
	}

	svc := pkg.NewService(repository)

	if err := pkg.OrderCreatedSubscriber(
		svc,
		os.Getenv("MESSENGER_HOST"),
		os.Getenv("MESSENGER_USERNAME"),
		os.Getenv("MESSENGER_PASSWORD"),
	); err != nil {
		log.Print(err)
	}
}
