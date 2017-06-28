from Strategy.DefaultStrategy import DefaultStrategy

import pika
import sys
import datetime
import socketIO_client
import json

kitSocketHost = '**'
kitSocketPort = **
kitMQHost = '**'
kitMQPort = **
kitMQUsername = '**'
kitMQPassword = '**'
kitMQQueueName = '**'
kitMQHeartBeat = 0


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


def kitMQListen(connection, queue_name):
    kitConsole('creating RabbitMQ.Channel(queue={})'.format(queue_name))
    channel = connection.channel()
    channel.queue_declare(queue=queue_name, durable=True)
    kitConsole('channel connected.')
    channel.basic_qos(prefetch_count=1)
    channel.basic_consume(kitConsumer, queue=queue_name)
    channel.start_consuming()


def kitConsumer(channel, method, properites, body):
    data = json.loads(body)
    if data['type'] == 'default':
        strategy = DefaultStrategy(socket, rabbitMQ, kitConsole)
    else:
        strategy = None
    if strategy is not None:
        strategy.start(period=20.0)
        strategy.process(data)
        strategy.end()
    channel.basic_ack(delivery_tag=method.delivery_tag)


if __name__ == '__main__':
    socket = kitConnectSocket(kitSocketHost, kitSocketPort, {'author': 'judger'})
    rabbitMQ = kitConnectRabbitMQ(kitMQHost, kitMQPort, kitMQUsername, kitMQPassword, kitMQHeartBeat)
    kitMQListen(rabbitMQ, kitMQQueueName)
