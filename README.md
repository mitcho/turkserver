turkserver
==========

Hosts Amazon Mechanical Turk-style surveys on your own server. Does not handle users, payment, etc. Requires LAMP (Linux, Apache, MySQL, PHP 5.3+) server.

Copyright 2013 Michael Yoshitaka Erlewine <mitcho@mitcho.com> and contributors. All code licensed under the MIT license. See license block below and in individual files.

## Installation

1. Copy the `turkserver` folder onto your server. The "turkserver" folder name will become part of survey URLs, so change the name of the directory if you like.
2. In the `turkserver` folder, make a copy of `config.sample.php` called `config.php`. Modify `config.php` to have the proper MySQL database connection details.
3. Try connecting to `http://...yourserver.../turkserver/install.php` . The `check` tool will verify whether the installation is complete.


## Usage

...


## TODO

* make sure multiple submissions with the same assignmentId is denied
* add missing MTurk fields in results files
* cookie workers with faux WorkerId
* validate the header row in record_results()
* create admin tool for managing experiments
* write installation script

## The MIT License (MIT)

Copyright (c) 2013 Michael Yoshitaka Erlewine and contributors

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
