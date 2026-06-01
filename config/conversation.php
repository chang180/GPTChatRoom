<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Conversation context window
    |--------------------------------------------------------------------------
    |
    | Number of recent user/gpt messages sent verbatim to the chat model.
    |
    */

    'context_limit' => (int) env('CONVERSATION_CONTEXT_LIMIT', 20),

    /*
    |--------------------------------------------------------------------------
    | Summarize batching
    |--------------------------------------------------------------------------
    |
    | Only run summarize when at least this many messages sit outside the recent
    | window and past the checkpoint. Reduces API calls during demo chats.
    |
    */

    'summarize_min_overflow' => (int) env('CONVERSATION_SUMMARIZE_MIN_OVERFLOW', 5),

    /*
    |--------------------------------------------------------------------------
    | Summarize output limits
    |--------------------------------------------------------------------------
    */

    'summarize_max_tokens' => (int) env('CONVERSATION_SUMMARIZE_MAX_TOKENS', 400),

    'summary_compress_max_tokens' => (int) env('CONVERSATION_SUMMARY_COMPRESS_MAX_TOKENS', 300),

    'summary_max_chars' => (int) env('CONVERSATION_SUMMARY_MAX_CHARS', 2000),

    'summary_context_max_chars' => (int) env('CONVERSATION_SUMMARY_CONTEXT_MAX_CHARS', 1500),

];
