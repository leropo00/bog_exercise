<?php

define('TABLE_USER_ACCOUNTS', 'user_accounts');
define('TABLE_TRANSACTIONS', 'transactions');

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
