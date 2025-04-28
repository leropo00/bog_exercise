<?php
/* Table names defintions */
define('TABLE_USER_ACCOUNTS', 'user_accounts');
define('TABLE_TRANSACTIONS', 'transactions');

/* API response fields */
define('API_RESPONSE_STATUS_FIELD',     'status');
define('API_RESPONSE_MESSAGE_FIELD',    'message');
define('API_RESPONSE_STATUS_SUCCESS',   'success');
define('API_RESPONSE_STATUS_ERROR',     'error');

define('PROCESS_TRANSACTION_MSG_TRANSACTION_SUCCESS',   'Transaction processed successfully');
define('PROCESS_TRANSACTION_MSG_ALREADY_PROCCESED',     'Transaction has already been processed');
define('PROCESS_TRANSACTION_MSG_BET_TOO_LARGE',         'Bet is larger, than possible based on user balance');
define('PROCESS_TRANSACTION_MSG_ALREADY_IN_PROGRESS',   'Transaction with same transaction_id already in progress');
define('PROCESS_TRANSACTION_MSG_SYSTEM_ERROR',          'System error occured while processing transaction');

/* Game logic definitions */
define('MIN_BET_VALUE', 10.0);

define('GAME_ROULETTE',  'roulette');
define('GAME_CRAPS',     'craps');
define('GAME_SLOTS',     'slots');

define("POSSIBLE_GAMES", implode(",", [GAME_ROULETTE, GAME_CRAPS, GAME_SLOTS]));

define('GAME_CRAPS_IMMEDIATE_WINS', [7, 11]);
define('GAME_CRAPS_IMMEDIATE_LOSS', [2, 3, 12]);

define('GAME_SLOTS_REELS_COUNT',           3);

define('GAME_SLOTS_SYMBOL_SEVEN',           'seven');
define('GAME_SLOTS_SYMBOL_BAR',             'bar');
define('GAME_SLOTS_SYMBOL_CHERRY',          'cherry');
define('GAME_SLOTS_SYMBOL_WATERMELLON',     'watermellon');

define('GAME_SLOTS_WINNINGS',  
    [
        GAME_SLOTS_SYMBOL_SEVEN => 160,
        GAME_SLOTS_SYMBOL_BAR => 25,
        GAME_SLOTS_SYMBOL_CHERRY => 8,
        GAME_SLOTS_SYMBOL_WATERMELLON => 4,
    ]
);
