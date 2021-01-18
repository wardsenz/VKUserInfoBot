package handlers

import (
	"encoding/json"
	"fmt"
	"gopkg.in/tucnak/telebot.v3"
	"io/ioutil"
	"log"
	"net/http"
	"net/url"
	"os"
)

type Response struct {
	Response []struct {
		FirstName       string `json:"first_name"`
		ID              int    `json:"id"`
		LastName        string `json:"last_name"`
		CanAccessClosed bool   `json:"can_access_closed"`
		IsClosed        bool   `json:"is_closed"`
		Sex             int    `json:"sex"`
		Online          int    `json:"online"`
		Verified        int    `json:"verified"`
		Domain          string `json:"domain"`
		Bdate           string `json:"bdate"`
		City            struct {
			ID    int    `json:"id"`
			Title string `json:"title"`
		} `json:"city"`
		Country struct {
			ID    int    `json:"id"`
			Title string `json:"title"`
		} `json:"country"`
		PhotoMax string `json:"photo_max"`
		Site     string `json:"site"`
		Status   string `json:"status"`
		LastSeen struct {
			Platform int `json:"platform"`
			Time     int `json:"time"`
		} `json:"last_seen"`
		FollowersCount   int  `json:"followers_count"`
		CanInviteToChats bool `json:"can_invite_to_chats"`
	} `json:"response"`
}

func (h *handlers) OnSearch(context telebot.Context) error {
	targetUrl := "https://api.vk.com/method/users.get?"
	requestParams := url.Values{}

	requestParams.Add("user_ids", context.Message().Payload)
	requestParams.Add("fields", "home_town,sex,relation,city,country,bdate,verified,status,online,last_seen,followers_count,site,domain,about,quotes,interests,personal,music,contacts,photo_max")
	requestParams.Add("access_token", os.Getenv("VK_TOKEN"))
	requestParams.Add("v", "5.126")

	resp, err := http.Get(targetUrl + requestParams.Encode())

	if err != nil {
		log.Fatal(err)
		return err
	}

	bodyBytes, err := ioutil.ReadAll(resp.Body)

	if err != nil {
		log.Fatal(err)
		return err
	}

	var r Response
	err = json.Unmarshal(bodyBytes, &r)

	if err != nil {
		log.Fatal(err)
		return err
	}

	for _, value := range r.Response {
		h.bot.Send(context.Sender(),
			fmt.Sprintf(
				"ID: %v\nИмя и фамилия: %v %v\nСтатус: %v",
				value.ID,
				value.FirstName,
				value.LastName,
				value.Status,
			),
		)
	}
	return nil
}
