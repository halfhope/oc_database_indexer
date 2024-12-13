# OpenCart Database Indexer
[![License: GPLv3](https://img.shields.io/badge/license-GPL%20V3-green?style=plastic)](LICENSE)

This script automatically checks and adds missing indexes to your store's database tables. Queries with indexes execute much faster than those without, significantly reducing the load on the database server. The script includes 27 known indexes for 14 tables.

## Other Languages

* [Russian](README_RU.md)

## Change Log

* [CHANGELOG.md](docs/CHANGELOG.md)

## Screenshots

* [SCREENSHOTS.md](docs/SCREENSHOTS.md)

## Features

* Automatically checks tables for missing indexes
* Adds required indexes if needed
* Optimizes tables upon completion using the database server's built-in `OPTIMIZE TABLE` function

## Compatibility

* Compatible with all versions of OpenCart

## Installation

* Download the script
* Open the file in a text editor and set a password to secure access
* Upload the file to your site's root directory
* Run it in a browser (e.g., `https://site.com/indexer.php`)

All indexes will be added automatically. **Don't forget to emove this file after work**.

## License

* [GPL v3.0](LICENSE.MD)

## Thank You for Using My Extensions!

I have decided to make all my OpenCart extensions free and open-source to benefit the community. Developing, maintaining, and updating these extensions takes time and effort.

If my extensions have been helpful for your project and you’d like to support my work, any donation is greatly appreciated.

### 💙 You can support me via:

* [PayPal](https://paypal.me/TalgatShashakhmetov?country.x=US&locale.x=en_US)
* [CashApp](https://cash.app/$TalgatShashakhmetov)

Your support inspires me to keep improving and developing these tools. Thank you!
