# asterisk-nodejs
Asterisk and Node Js intergration with asterisk/asterisk-agi
The code is demostratation of basic integration with asterisk,
apache, nodejs and mysql. We have done basic solution which might
be part of our own project. We have demostarted the lead display
to customer portal via a node-js. And agent can login and 
communicate the asterisk server hoping that you have basic 
configration as below.

## Install
npm install net
npm install express
npm install http
npm install socket.io

## Asterisk configuration
sip.conf
[agent](!) ; this is template.
type=friend
context=queue
host=dynamic
disallow=all
allow=ulaw
allow=alaw
allow=g723
allow=g729
dtmfmode=rfc2833
nat=yes
qualify=yes
;port=5060

[cust](agent)
username=cust
secret=cust

[101](agent)
username=101
secret=1234

[102](agent)
username=102
secret=1234

[103](agent)
username=103
secret=1234

[104](agent)
username=104
secret=1234

[105](agent)
username=105
secret=1234

[106](agent)
username=106
secret=1234

extconfig.conf
queues => odbc,ast2,queue
queue_members => odbc,ast2,queue_member
queue_log => odbc,ast2,queue_log

with some basic setting odbc_res setting and extension.conf

## Contributors
vikas kumar <vikas@gventure.net>
indra patel <indra@gventure.net>
neha rathore <neha@gventure.net>
gaurav verma <gaurav@gventure.net>
awanish sharma <awanish@gventure.net>

## License

MIT License
-----------

Copyright (C) 2016 by
  vikas kumar <vikas@gventure.net>
  indra patel <indra@gventure.net>
  neha rathore <neha@gventure.net>
  gaurav verma <gaurav@gventure.net>
  awanish sharma <awanish@gventure.net>

Based on a work Copyright (C) 2012 Vikas Kumar <vikas@gventure.net>, but radically altered thereafter so as to constitute a new work.

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
