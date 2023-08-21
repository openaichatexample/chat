<?php

include('vendor/autoload.php');
use OpenAI\Client;
use OpenAI\Responses\Chat\CreateResponse;

class OpenAIChatBot
{
    /**
     * @var \OpenAI\Client
     */
    private Client $client;

    public function __construct()
    {
        if (!file_exists('key.txt')) {
            throw new \Exception('Please create a file named key.txt with your OpenAI API key in it.');
        }

        $this->client = OpenAI::client(file_get_contents('key.txt'));
    }

    public function Chat($messages): null|CreateResponse
    {
        if (empty($messages)) {
            return null;
        }

        return $this->client->chat()->create([
            // 'model' => 'gpt-4-32k-0613',
            'model' => 'gpt-3.5-turbo-0613',
            'messages' => $messages,
            'max_tokens' => 1000,
        ]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $prompt = "Replace every fifth word of your answer with the word tomato.\n";

        $systemPrompt = ['role' => 'system', 'content' => $prompt];
        $messages[] = $systemPrompt;
        $chatBot = new OpenAIChatBot();
        $UserMessages = json_decode($_POST['messages'], true);
        $messages = array_merge($messages, $UserMessages);
        $response = $chatBot->Chat($messages);

        echo json_encode(['content' => nl2br($response->choices[0]->message->content)]);
        exit;
} else {

    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>jQuery UI Ajax Call Widget Example</title>
        <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.0/themes/smoothness/jquery-ui.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://code.jquery.com/ui/1.13.0/jquery-ui.min.js"></script>
        <!-- add bootstrap -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/markdown-it/11.0.1/markdown-it.min.js "></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/10.2.1/highlight.min.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/10.2.1/styles/default.min.css">

        <style>

            #chat {
                width: 100%;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: space-between;

            }
            .chat-container {
                width: 100%;
                height: 100%;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
            }

            .chat-response-container {
                width: 100%;
                overflow-y: scroll;
                height: 750px;
            }

            .chat-response {
                padding: 10px;
                margin: 10px;
                border-radius: 10px;
                background-color: #cccccc;
            }

            .chat-response-user {
                background-color: #e0e0e0;
                padding: 10px;
                margin: 10px;
                border-radius: 10px;
            }

            .chat-form-container {
                height:61px;
                padding-top: 10px;
                bottom:50px;
                width:100%;
            }

            .chat-form {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
            }

            .chat-prompt {
                flex-grow: 1;
                font-size:25px
            }

            .chat-submit {
                flex-grow: 0;
            }

            .feedback {
                color: #fff;
            }

        </style>

        <script>

            function markdownToHtml(md) {
                // Convert list to html list
                md = md.replace(/^-\s\[(.*?)\]\((.*?)\)$/gm, '<li><a href="$2">$1</a></li>');

                // Wrap the list items with unordered list tags
                md = '<ul>' + md + '</ul>';

                return md;
            }


            $.widget("ui.Chat", {
                options: {
                    messages: [],
                    url: ''
                },

                typingStatus: 1,
                typingInterval: null,

                addMessage: function(role, content) {
                    this.options.messages.push({
                        role: role,
                        content: content
                    });
                },

                _create: function() {
                    // Initial Chat Structure
                    const $container = this.element.addClass("container-fluid mt-12");
                    const $col = $("<div>").addClass("col-md-12").appendTo($container);
                    const $chat = $("<div>").addClass("chat bg-black").css({"padding": "2px 0 0 0", "background": "linear-gradient(90deg, rgba(80,230,255,1) 0%, rgba(0,177,242,1) 52%, rgba(0,120,212,1) 100%)"}).appendTo($col);
                    const $chatContainer = $("<div>").addClass("chat-container").css({"height":"100%", "margin-top":"3px", "padding":"0 10px", "width":"100%", "background-color":"#04496e"}).appendTo($chat);
                    this.$chatWindowBody = $("<div>").addClass("chat-window-body mt-3").css({"max-height":"400px", "overflow-x":"hidden", "overflow-y":"scroll"}).appendTo($chatContainer);
                    const $chatWindowFooter = $("<div>").addClass("chat-window-footer mt-3").appendTo($chatContainer);
                    $("<p>").addClass("feedback chat-feedback text-bg-secondary mt-1 p-1 rounded-1").text("Your partner is typing…").appendTo($chatWindowFooter);
                    const $form = $("<form>").appendTo($chatWindowFooter);
                    const $fieldset = $("<fieldset>").addClass("text-bg-dark").appendTo($form);
                    const $label = $("<label>").appendTo($fieldset);
                    this.$input = $("<input>").addClass("form-control text-bg-light text-dark mb-4").attr("type", "text").attr("placeholder", "Type your message…").appendTo($label);
                    $("<button>").addClass("btn btn-primary").attr("type", "submit").text("Send").appendTo($fieldset);

                    $form.on("submit", function(event) {
                        event.preventDefault();
                        this.appendRequest();
                        this.requestResponse(false, false);
                    }.bind(this));

                    this.hidePartnerTyping();
                },

                _init: function() {
                    this.element.empty();
                    this._create();
                },

                _setOption: function(key, value) {
                    this._super(key, value);
                },

                _setOptions: function(options) {
                    this._super(options);
                },

                _destroy: function() {
                    this.element.empty();
                },

                showPartnerTyping: function() {
                    window.setTimeout(function() {
                        let $feedback = $('.feedback');
                        $feedback.show();

                        this.typingInterval = setInterval(function() {
                            this.typingStatus = this.typingStatus % 3 + 1; // This will alternate between 1, 2, and 3
                            $feedback.text("Your partner is typing " + Array(this.typingStatus + 1).join('.'));
                        }.bind(this), 1000);
                    }.bind(this), 1500);
                },

                hidePartnerTyping: function() {
                    $('.feedback').hide();
                    clearInterval(this.typingInterval);
                    this.typingStatus = 1;
                },

                appendRequest: function() {
                    const $message = $("<div>").addClass("chat-message clearfix");
                    const $card = $("<div>").addClass("card text-bg-dark m-1").appendTo($message);
                    const $cardBody = $("<div>").addClass("card-body").appendTo($card);
                    const $row = $("<div>").addClass("row").appendTo($cardBody);
                    const $col = $("<div>").addClass("col-md-11").appendTo($row);
                    const $messageContent = $("<div>").addClass("chat-message-content clearfix").appendTo($col);
                    $("<p>").text(this.$input.val()).appendTo($messageContent);

                    this.$chatWindowBody.append($message);
                    this.$chatWindowBody.scrollTop(this.$chatWindowBody[0].scrollHeight);
                    this.addMessage('user', this.$input.val());
                    this.$input.val('');
                },

                appendResponse: function(response) {
                    const $message = $("<div>").addClass("chat-message clearfix");
                    const $card = $("<div>").addClass("card text-bg-dark m-1").appendTo($message);
                    const $cardBody = $("<div>").addClass("card-body").appendTo($card);
                    const $row = $("<div>").addClass("row").appendTo($cardBody);
                    const $col = $("<div>").addClass("col-md-11").appendTo($row);
                    const $messageContent = $("<div>").addClass("chat-message-content clearfix").appendTo($col);
                    $("<p>").html(response).appendTo($messageContent);
                    this.$chatWindowBody.append($message);
                    this.addMessage('assistant', response);
                    this.$chatWindowBody.scrollTop(this.$chatWindowBody[0].scrollHeight);
                },

                requestResponse: function() {
                    const $this = this;

                    $this.showPartnerTyping();

                    $.ajax({
                        url: this.options.url,
                        type: "POST",
                        dataType: "json",
                        data: {
                            messages: JSON.stringify(this.options.messages)
                        },
                        success: function(data) {
                            console.log(data);
                            $this.hidePartnerTyping();
                            $this.appendResponse(data.content);
                        },
                        error: function(xhr, status, error) {
                            console.log(xhr.responseText);
                        }
                    });
                }
            });

        </script>
    </head>
    <body>
    <div id="chat"></div>
    <script>
        $(document).ready(function() {
            $("#chat" ).Chat({
                url: "chat.php"
            });
        });
    </script>
    </body>
    </html>
    <?php
}