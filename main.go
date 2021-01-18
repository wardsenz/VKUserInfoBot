package main

import (
	h "VKUserInfoBot/handlers"
	"gopkg.in/tucnak/telebot.v3"
	"log"
	"os"
)

func main() {
	bot, err := telebot.NewBot(telebot.Settings{
		Token: os.Getenv("BOT_TOKEN"),
		Poller: &telebot.LongPoller{
			Timeout: 10,
		},
	})

	if err != nil {
		log.Fatal(err)
		return
	}

	//ch, err := cache.NewCache(os.Getenv("REDIS_URL"))
	//
	//if err != nil {
	//	log.Fatal(err)
	//	return
	//}

	handler := h.InitHandlers(bot)

	bot.Handle("/vk", handler.OnSearch)

	bot.Start()

}
