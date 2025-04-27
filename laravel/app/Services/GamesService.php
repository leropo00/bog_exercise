<?php

namespace App\Services;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

use App\Enums\PurchaseItemStatus;
use App\Models\PurchaseItem;
use App\Models\PurchaseListEvent;

class GamesService
{
    /**
     * Returns bet winnings
     * Value returned as 0 here means no winnings
     */
    public function calculateBetWinnings($betAmount, $gamePlayed): float 
    {
        // simulation of long running background task
        sleep(1); 

        if ($gamePlayed == GAME_ROULETTE) {
            return $this->playRoulette($betAmount);
        }
        else if ($gamePlayed == GAME_CRAPS) {
            return $this->playCraps($betAmount);
        }
        else if ($gamePlayed == GAME_SLOTS) {
            return $this->playSlots($betAmount);
        }
    }

    /* This simulates simulates bet on red or black pocket in a roulette game,
        payout is 1:1 in relation to bet placed.
        Roulette wheel has 38 slots: numbers 1–36, 0, and 00,
        18 slots are red, 18 slots are black, and 2 slots are green.
        In this simulation, first 18 numbers, mean color match
    */
    private function playRoulette($betAmount): float 
    {
        return $rand(1, 38) <= 18 ? $betAmount * 2 : 0.0;
    }
    
    /*
        This simulates Pass Line Bet in a game of craps,
        payout is 1:1 in relation to bet placed. 
        
        rules are the following, two dices, with numbers 1 - 6 are rolled, 
        based on the first role, one of the following scenarios occurs:

        1.) Immediate Win: If the first roll is a 7 or 11, 
            you immediately win your bet, the payout is 1:1 (even money).

        2.) Immediate Loss: If the first roll is a 2, 3, or 12, you lose your bet.
        
        3.) Point Phase: If the first roll is 4, 5, 6, 8, 9, or 10, this number becomes the point. 
            The shooter continues rolling until either the point number 
            is rolled again (you win) or a 7 is rolled (you lose).
    */
    private function playCraps($betAmount): float 
    {
        $initialValue = $this->simulateTwoDicesRoll();

        if (in_array($initialValue, GAME_CRAPS_IMMEDIATE_WINS)) {
            return $betAmount * 2;
        }
        else if (in_array($initialValue, GAME_CRAPS_IMMEDIATE_LOSS)) {
            return 0;
        }

        // we are in poins phase, now
        // I personaly added limit on rolls here, if limit is reached it assumes no winnings

        $i = 1;
        while ($i < 1000) {
            $rollValue = $this->simulateTwoDicesRoll();
            if ($rollValue == 7) {
                return 0;
            }
            else if ($rollValue == $initialValue) {
                return $betAmount * 2;
            }
            $i++;
        }

        return 0.0;
    }

    /* 
        This simulates game of slots, here slots have 3 reels and 4 unique symbols.
        Each reel has 10 symbols, and count of each symbol is the same in each reel
        Each reel has one  7, two bars, three cherries and four watermelons.
        If 3 symbols match   
    */

    private function playSlots($betAmount): float 
    {

        $slotSpin = $this->simulateSlotSpin();
        $counts = [
            GAME_SLOTS_SYMBOL_SEVEN        => 0,
            GAME_SLOTS_SYMBOL_BAR          => 0,
            GAME_SLOTS_SYMBOL_CHERRY       => 0,
            GAME_SLOTS_SYMBOL_WATERMELLON  => 0,
        ];

        /*
        Numbers are used here to represent symbols, this are the mappings 
            1 - 4:  watermelon
            5 - 7:  cherries
            8 - 9:  bars
            10:     sevens
        */
        foreach ($slotSpin as $symbol) {
            if ($symbol == 10) {
                $counts[GAME_SLOTS_SYMBOL_SEVEN]++;
            }
            else if ($symbol >= 8 && $symbol <= 9) {
                $counts[GAME_SLOTS_SYMBOL_BAR]++;
            }
            else if ($symbol >= 5 && $symbol <= 7) {
                $counts[GAME_SLOTS_SYMBOL_CHERRY]++;
            }
            else if ($symbol >= 1 && $symbol <= 4) {
                $counts[GAME_SLOTS_SYMBOL_WATERMELLON]++;
            }
        }

        $matchingSymbol = array_search(GAME_SLOTS_REELS_COUNT, $counts);
        if ($matchingSymbol !== false) {
            // based on probabily of match diffent bet winnings are retured, 
            return GAME_SLOTS_WINNINGS[ $matchingSymbol] * $betAmount;
        }
        // no match so no winnings
        return 0.0;
    }

    private function simulateTwoDicesRoll(): int 
    {
        // two separate randoms are used
        // since in actual dice rolls,
        // certain outcomes like 7, are more likely
        // than others like 2 or 12.
        return rand(1, 6) + rand(1, 6);
    }

    private function simulateSlotSpin(): array 
    {
        $results = [];
        for ($i = 0; $i < GAME_SLOTS_REELS_COUNT; $i++) {
            $results[] = rand(1, 10);
        }
        return $results;
    }
}