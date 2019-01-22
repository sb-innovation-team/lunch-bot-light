# Lunchbot Light

This bot was written under a rapid timeframe, take note that the code requires refactoring before extending it any further.

## Dependencies

- https://github.com/palanik/slack-php

---

## Installation

- Setup your Slack Workspace with the correct commands and token
- Copy `.env.example` to `.env` file.
- Fill in correct parameters for `.env` file.
- `$ composer install`
- `$ php artisan migrate`

Everything should work now.

---

## Usage

### Available Commands

- `/register <email_address>` Registers (and checks) your email address to use the bot! 
    (Email instead of UserID so you can switch accounts), links all that to your Slack UserID
- `/deposit <amount>` Adds an amount of euros to your balance.
- `/happyaccident` Undoes your last deposit. Adds an entry of your happy accident in the Transactions. Big Brother sees all.
- `/balances` Gets all balances of every user in an ascending matter.
- `/transactions` Gets all recent transactions.
- `/hungry` Puts you on the eat-list, tells you your balance, how much Lunch costs today, how many people already signed up.
- `/overview` Prints an overview in #general with the Lunch Balance, how many people are attending.

### Automated Tasks

- **At lunchtime exact;** Drops a cheeky "Bon appetit!" in the designated lunch channel
- **During the weekend;** Refuse all commands, even bots need weekends off, you know?
