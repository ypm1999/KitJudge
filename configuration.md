# Configuration

This is a step-by-step introduction to the installation of KitOJ.

## Requirements

There are a few limits to the OS:

* Ubuntu 64-bit operating system is installed with version ```>=14.04```.
* ```200MB``` free disk space.

## Conceptions

Before reading detailed installation instructions, a few conceptions should be known first to better understand where and why we use this plug-in.

* Main Server:  ```Main Server``` is designed to provide the interface of the whole system, namely the website server. And it also provides a ```git/ssh``` server to transmit ```problem repository```.
* Database Server: ```Database Server``` is designed to store and transmit all the data used by the ```Main Server``` and the ```Judge Server```.
* Stream Server: ```Stream Server``` is used to dynamic transmit the judge runtime status from ```Judge Server``` to ```Main Server``` and finally to the client.
* Judge Server: ```Judge Server``` is the server to handle all the judge tasks.
* Work Queue Server: ```Work Queue``` server is designed to manage the judge tasks and distribute them to the ```Judge Server``` uniformly.

The five kinds of servers can be run on the same computer. But run ```Judge Server``` on another computer is suggested due to judge task being resource-consuming.

## Platform Installation

Web servers application must be installed firstly to run the website.

### Apache2

```Apache2``` is required to be installed on the ```Main Server```.

```bash
sudo apt-get install apache2
```

### PHP 7.0

```PHP7.0``` is required to be installed on the ```Main Server```.

```bash
sudo apt-get install php
sudo apt-get install php7.0-dev
sudo apt install libapache2-mod-php7.0
```

### MySQL

#### For Database Server

```bash
sudo apt-get install mysql-server
sudo apt-get install libmysqlclient-dev
sudo apt-get install php-mysql
```

#### For Main Server and Judge Server

```bash
sudo apt-get install libmysqlclient-dev
```

### Python-PIP

```python-pip``` is required to be installed on the ```Main Server```.

```bash
sudo apt-get install python-pip
```

### GIT

#### For Main Server

```bash
sudo apt-get install git
sudo apt-get install openssh-server
```
#### For Judge Server

```bash
sudo apt-get install git
sudo pip install gitpython
```

### VALGRIND*

```valgrind``` is optimally required to be installed on the ```Judge Server``` if memory checkers are needed.

```bash
sudo apt-get install valgrind
```

### NodeJS

```nodejs``` is required to be installed on ```Main Server```.

```bash
sudo apt-get install nodejs-legacy
```

### SocketIO

#### For Main Server

```bash
npm install socketio
```

#### For Judge Server

```bash
sudo pip install socketIO_client
```

### RabbitMQ

```rabbitMQ``` is required to be installed on ```Work Queue Server```.

```bash
wget https://www.rabbitmq.com/rabbitmq-signing-key-public.asc
sudo apt-key add rabbitmq-signing-key-public.asc
rm rabbitmq-signing-key-public.asc
sudo apt-get install rabbitmq-server
sudo apt-get install librabbitmq-dev
sudo pip install pika
```

### Install Requirements for PHP

```bash
sudo composer install
```

## Platform Configuration

### Git on Main Server

```bash
cd files/probfile
git init
git add .
git config user.name "<your name>"
git config user.email "<your email>"
git commit -m "Initial commit."
git remote add origin /path/to/files/probfile
git push origin master
cd ~
ln -s /path/to/files/probfile probfile
```

### PHP on Main Server

Add the following one line to ```/etc/php/7.0/apache2/php.ini```

```bash
extension=amqp.so
```

### Apache2 on Main Server

Change ```/etc/apache2/apache2.conf```

```bash
<Directory /var/www>
  ...
  AllowOverride **All** # modify here from None to All
  ...
</Directory>
```

Enable ```rewrite``` module in ```apache2```:

```bash
cd /etc/apache2/mods-enabled/
sudo ln -s ../mods-available/rewrite.load rewrite.load
```

### RabbitMQ on Work Queue Server

```bash
sudo rabbitmqctl add_user <your MQ username> <your MQ password>
sudo rabbitmqctl set_user_tags <your MQ username> administrator 
sudo rabbitmqctl set_permissions -p '/' <your MQ username> ".*" ".*" ".*"
```

### MySQL on Database Server

First login into MySQL server:

```bash
mysql -u <your mysql root username> -p
# input your mysql root password
```

Then execute the following commands:

```mysql
CREATE USER '<your mysql kitoj username>'@'%' IDENTIFIED BY '<your mysql kitoj password>';
GRANT ALL PRIVILEGES ON KitJudge.* TO '<your mysql kitoj username>'@'%' WITH GRANT OPTION;
```

Last, modify the MySQL ```conf``` file (Usually, that is ```/etc/mysql/mysql.conf.d/mysqld.cnf```):

```bash
# Change "bind-address=127.0.0.1" to "# bind-address=127.0.0.1"
```

## Website Configuration

#### KitInfo.php On Main Server

```KitInfo.php```(In folder ```application/libraries/```) is a file that contains all needed information used by ```Main Server```.  You can fill out each variable by substituting ```**``` to the actual value.

#### judger.py & Strategy.py On Judge Server 

You should subsitute all of the double starts(namely ```**```) in the file ```judger.py``` (In the folder ```tools/server/judger```) and in the file ```Strategy.py``` (In the folder ```tools/server/judger/Strategy/```)

#### deamon.py On Judge Server

You should subsitute all the double starts(namely ```**```) in the file ```deamon.py``` (In the folder ```tools/deamon/```).
