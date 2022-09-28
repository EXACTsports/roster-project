# Roster Data Scraping Project

> :warning: **Please fork this repository**: Create a fork of this repository and then submit a pull request when you are ready for us to review it.

We are exploring the idea of scraping college team rosters for information on their current athletes. For this project, 
you will receive a spreadsheet of collete team rosters. You'll import those rosters into the `rosters` table. You'll 
then write a command that will scrape those rosters and extract as much information about the athletes on the rosters as
possible. The only required column for each athlete is name. Everything else is nullable. Write your scraper so that it 
will extract as much information as possible about each athlete. If you can extract information about atheltes that
doesn't have a dedicated column in the `athletes` table, there's a json column called `extra` where you can store this
data as key / value pairs. For example: `['major' => 'Engineering', 'birthday' => 'August 10, 2003']`.

Don't worry much about the formatting of the data you're collecting. We will decide how to format, standardize, and
validate it later. For this project, we are looking for volume of data.

Use whatever method you want to scrape. The obvious choices are the Laravel `Http` facade and Spatie's Browsershot. The
main thing we're going to look for here is how well your solution could scale up to be used to scrape this sort of data
from every college team roster in the country.

This is a bare-bones Laravel app that contains only the migrations needed for the two tables (rosters and athletes) and 
empty model for each. If you have a good reason for changing a migration or creating new ones, go for it. Do what you
want with the models.
