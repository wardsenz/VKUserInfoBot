package handlers

import (
	"gopkg.in/tucnak/telebot.v3"
)

type handlers struct {
	bot *telebot.Bot
	//cache *redis.Client
}

func InitHandlers(bot *telebot.Bot) *handlers {
	return &handlers{bot: bot}
}
