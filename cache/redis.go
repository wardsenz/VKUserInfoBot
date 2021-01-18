package cache

import "github.com/go-redis/redis"

func NewCache(url string) (*redis.Client, error) {
	parsedUrl, err := redis.ParseURL(url)

	if err != nil {
		return nil, err
	}

	rdb := redis.NewClient(&redis.Options{
		Addr:     parsedUrl.Addr,
		Password: parsedUrl.Password,
		DB:       parsedUrl.DB,
	})

	if err := rdb.Ping().Err(); err != nil {
		return nil, err
	}

	return rdb, nil

}
