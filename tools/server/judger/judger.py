from Strategy.DefaultStrategy import DefaultStrategy
from Strategy.OIStrategy import OIStrategy

import pika
import sys
import datetime
import socketIO_client
import json
import time

kitSocketHost = '**'
kitSocketPort = **
kitMQHost = '**'
kitMQPort = **
kitMQUsername = '**'
kitMQPassword = '**'
kitMQQueueName = '**'
kitMQHeartBeat = 20


def kitConsole(message):
    sys.stderr.write('  ' + datetime.datetime.now().strftime('[%b-%d-%y %H:%M:%S] ') + str(message) + '\n')
    sys.stderr.flush()


def kitConnectSocket(host, port, args):
    kitConsole('connecting sockets(host={}, port={}, args={})'.format(host, port, args))
    connection = socketIO_client.SocketIO(host=host, port=port, params=args)
    kitConsole('socket connected.')
    return connection


def kitConnectRabbitMQ(host, port, username, password, heartbeat):
    kitConsole('connecting RabbitMQ(host={}, port={}, user={}, heartbeat={})'.format(host, port, username, heartbeat))
    connection = pika.BlockingConnection(pika.ConnectionParameters(
        host=host,
        port=port,
        credentials=pika.PlainCredentials(
            username=username,
            password=password
        ),
        heartbeat_interval=heartbeat
    ))
    kitConsole('RabbitMQ connected.')
    return connection

def kitGetRabbitMQChannel(connection, queue_name):
    kitConsole('creating RabbitMQ.Channel(queue={})'.format(queue_name))
    channel = connection.channel()
    channel.queue_declare(queue=queue_name, durable=True)
    channel.basic_qos(prefetch_count=1)
    channel.basic_consume(kitConsumer, queue=queue_name)
    kitConsole('channel created.')
    return channel

def kitTaskEnded(channel, method):
    kitConsole('telling server the task is processed...')
    while True:
        try:
            channel.basic_ack(delivery_tag=method.delivery_tag)
            kitConsole('told.')
            break
        except:
            kitConsole('failed to execute channel.basic_ack, trying to recreate channel...')
            while True:
                try:
                    channel = kitGetRabbitMQChannel(rabbitMQ, kitMQQueueName)
                    kitConsole('channel recreated.')
                    break
                except:
                    kitConsole('failed to recreate channel, trying to reconnect server...')
                    while True:
                        try:
                            global rabbitMQ
                            rabbitMQ = kitMQConnector()
                            kitConsole('server reconnected.')
                            break
                        except:
                            kitConsole('failed to reconnect server, waiting to reconnect again in 5 second(s)...')
                            time.sleep(5)


def kitConsumer(channel, method, properites, body):
    data = json.loads(body)
    if data['type'] == 'default':
        strategy = DefaultStrategy(socket, rabbitMQ, kitConsole, kitMQConnector)
    elif data['type'] == 'OI':
        strategy = OIStrategy(socket, rabbitMQ, kitConsole, kitMQConnector)
    else:
        strategy = None
    if strategy is not None:
        strategy.start(period=20.0)
        strategy.process(data)
        strategy.end()
    kitTaskEnded(channel, method)


def kitMQConnector():
    return kitConnectRabbitMQ(kitMQHost, kitMQPort, kitMQUsername, kitMQPassword, kitMQHeartBeat)


def kitSocketConnector():
    return kitConnectSocket(kitSocketHost, kitSocketPort, {'author': 'judger'})

if __name__ == '__main__':
    socket = kitSocketConnector()
    rabbitMQ = kitMQConnector()
    channel = kitGetRabbitMQChannel(rabbitMQ, kitMQQueueName)
    channel.start_consuming()
