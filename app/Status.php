<?php

namespace App;

use Carbon\Carbon;
use Cache;
use GuzzleHttp\Client;

class Status
{
    public $author;
    public $post;
    public $permalink;
    public $attachment;
    public $link;
    public $updated_time;

    public function __construct(array $attributes = [])
    {
        $this->author = $attributes['author'];
        $this->post = $attributes['post'];
        $this->permalink = $attributes['permalink'];
        $this->attachment = $attributes['attachment'];
        $this->updated_time = $attributes['updated_time'];
        $this->link = $attributes['link'];
    }

    static function make($feed) {
        return $feed->map( function($wall_post) {
                $message = $wall_post['message'] ?? false;
                $message = $message ? $message : false;
                $time = new Carbon($wall_post['updated_time'], 'America/New_York');

                $attachment = $wall_post['attachments']['data'][0]['media']['image']['src'] ?? false;

                if($attachment) {
                    $url_query = parse_url($attachment, PHP_URL_QUERY);
                    parse_str($url_query, $query);
                    $attachment = $query['url'];
                }

                
                $object = [
                    'author' => $wall_post['from']['name'], 
                    'post' => $message,
                    'permalink' => 'https://facebook.com/' . $wall_post['id'],
                    'link' => $wall_post['link'] ?? false,
                    'attachment' => $attachment,
                    'updated_time' => $time
                ];

                return ($message) ? new Status($object) : false;
            })->filter( function( $update ) {
                return ($update !== false);
        });
    }

    function sendToSlack() {
            $client = new Client();
            $record = Cache::rememberForever($this->permalink, function() use ( $client )  {
                
                // Random Colors for SlackAttachment
                $colors = ['#8EE5CF', '#9ECC90', '#9AD6B6', '#B4EDE0', '#B6D8B1'];
                shuffle($colors);

                // Additional Fields
                $fields = [
                    [
                        'title' => 'Posted By',
                        'value' => $this->author,
                        'short' => true
                    ]
                ];

                if($this->link) {
                    $fields[] = [
                        'title' => 'Attachment',
                        'value' => '<' . $this->link . '|Link>',
                        'short' => true
                    ];
                }

                $response = $client->request('POST', env('SLACK_HOOK_URL'), [
                    'json'    => [
                        // 'channel' => '@katz',
                        // 'text' => 'New Post to Facebook Group',
                        'attachments' => [
                            [
                                'title' => '<' . $this->permalink . '|Facebook Post to Group> on ' . $this->updated_time->setTimezone('America/New_York')->format('l - F jS, Y \\a\\t g:i:s a'),
                                'text' => $this->post . "\n\n",
                                'color' => $colors[0],
                                'thumb_url' => $this->attachment,
                                'fields' => $fields
                            ]
                        ]
                    ]
                ]);

                return $response;
            });

            return $this;
    }
}
