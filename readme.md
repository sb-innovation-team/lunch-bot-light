# Lunchbot Light

## Dependencies

- https://github.com/palanik/slack-php

---

## Installation

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
- `/budget` Prints the estimated budget for todays lunch (calculated by the amount of people signing up).

### Automated Tasks

- **An hour before Lunch;** Announces Lunch, be there or be square!
- **At lunchtime exact;** *&&* **There are attendees;** Subtracts the amount of money that lunch costs from every attendee's balance, adds it to the Lunch Balance.
- **At lunchtime exact;** Drops a cheeky "Bon appetit!" in #general
- **During the weekend;** Refuse all commands, even bots need weekends off, you know?