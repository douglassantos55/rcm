package main

import (
	"log"
	"os"
	"sync"

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

	conn, err := pkg.Connect(
		os.Getenv("MESSENGER_HOST"),
		os.Getenv("MESSENGER_USERNAME"),
		os.Getenv("MESSENGER_PASSWORD"),
	)

	if err != nil {
		log.Fatal(err)
	}

	defer conn.Close()

	var wg sync.WaitGroup
	svc := pkg.NewService(repository)

	wg.Add(3)

	go func(svc pkg.Service) {
		defer wg.Done()

		channel, err := conn.Channel()
		if err != nil {
			log.Fatal(err)
		}

		defer channel.Close()

		if err := pkg.RentCreatedSubscriber(svc, channel); err != nil {
			log.Fatal(err)
		}
	}(svc)

	go func(svc pkg.Service) {
		defer wg.Done()

		channel, err := conn.Channel()
		if err != nil {
			log.Fatal(err)
		}

		defer channel.Close()

		if err := pkg.RentUpdatedSubscriber(svc, channel); err != nil {
			log.Fatal(err)
		}
	}(svc)

	go func(svc pkg.Service) {
		defer wg.Done()

		channel, err := conn.Channel()
		if err != nil {
			log.Fatal(err)
		}

		defer channel.Close()

		if err := pkg.RentDeletedSubscriber(svc, channel); err != nil {
			log.Fatal(err)
		}
	}(svc)

	wg.Wait()
}
