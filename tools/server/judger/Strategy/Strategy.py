import traceback
from abc import abstractmethod

import json
import uuid
import os
import commands
import shutil
import pika
import git

from threading import Timer

import time, MySQLdb

kitReportMQHost = '**'
kitReportMQPort = **
kitReportMQUsername = '**'
kitReportMQPassword = '**'
kitReportMQQueueName = '**'
kitReportMQHeartBeat = 0
kitGitHost = '**'
kitGitUser = '**'
kitDBHost = '**'
kitDBPort = **
kitDBUsername = '**'
kitDBPassword = '**'
kitDBName = '**'


class Strategy:
    def __init__(self, socket, connection, console):
        self.__last_emit_case = 0
        self.__connection = connection
        self.__socket = socket
        self.__console = console
        self._buffer = {}

    @staticmethod
    def _readfile(filepath, byte=200):
        fp = open(filepath, 'r')
        ret = fp.read(byte + 1)
        if len(ret) == byte + 1:
            ret = ret[0:byte] + '...'
        fp.close()
        return ret

    def _gen_tmp_dir(self, caseId):
        path = 'tmp/' + str(self._kitRunId) + '-' + str(self._kitProbId) + '-' + str(caseId) + '-' + str(uuid.uuid1())
        os.makedirs(path)
        return path

    def _copy_includes(self, tmpdir):
        for inc in self._conf['include']:
            shutil.copy('probfile/' + str(self._kitProbId) + '/' + inc, tmpdir)

    def _move_judger(self, case_id, judge_conf, run_path):
        pre_judge_path = self._gen_tmp_dir(case_id)
        if judge_conf['type'] == 'default':
            shutil.copy('comparator/' + judge_conf['path'], pre_judge_path)
        else:
            shutil.copy('probfile/' + self._kitProbId + '/' + judge_conf['path'], pre_judge_path)
        if judge_conf['lang'] != 'exec':
            if judge_conf['lang'] == 'C++':
                if not self._compile('/usr/bin/g++ ' + judge_conf['path'] + ' -o __judger', judge_conf['lang'],
                                     pre_judge_path, verbose=False):
                    self._buffer.setdefault('verdict', 6)
                    self._buffer.setdefault('report', 'cannot compile the special judge "' + judge_conf['path'] + '"')
                    return
            else:
                self._buffer.setdefault('verdict', 6)
                self._buffer.setdefault('report', 'cannot compile the special judge "' + judge_conf['path'] + '"')
                return
            shutil.copy(pre_judge_path + '/__judger', run_path + '/__judger')
        else:
            shutil.copy(pre_judge_path + '/' + judge_conf['path'], run_path + '/__judger')

    def _compile(self, command, lang, tmpdir, verbose=True):
        rtype = None
        if lang == 'C++':
            rtype = 'compiler-c++'
        elif lang == 'C++ 11':
            rtype = 'compiler-c++'
        elif lang == 'C++ 14':
            rtype = 'compiler-c++'
        elif lang == 'C++ 17':
            rtype = 'compiler-c++'
        elif lang == 'Java':
            rtype = 'compiler-java'
        if rtype is None:
            if verbose:
                self._buffer.setdefault('report', 'Compilation failure: unknown programming language')
                self._buffer.setdefault('verdict', 10)
            return False
        else:
            result = self._execute(rtype=rtype, serr="error_compile.txt", work_path=tmpdir, runcmd=command)
            runcode = int(result[0].split(' ')[0])
            if runcode != 0 or int(result[2].split(' ')[2]) != 0:
                if verbose:
                    self._buffer.setdefault('verdict', 10)
                    if runcode == 0:
                        self._buffer.setdefault('report', self._readfile(tmpdir + '/error_compile.txt').decode('utf-8', 'ignore'))
                    elif runcode == 1:
                        self._buffer.setdefault('report', 'Compilation failure: compilation time limit exceed.')
                    elif runcode == 2:
                        self._buffer.setdefault('report', 'Compilation failure: compiler crashed("{}").'.format(
                            result[1].rstrip('\n')))
                    elif runcode == 3:
                        self._buffer.setdefault('report', 'Compilation failure: compilation output limit exceed')
                    elif runcode == 4:
                        self._buffer.setdefault('report', 'Compilation failure: compilation memory limit exceed.')
                    elif runcode == 5:
                        self._buffer.setdefault('report', 'Compilation failure: dangerous compilation')
                    elif runcode == 6:
                        self._buffer.setdefault('report', 'Compilation failure: failed to execute the compilation')
                    else:
                        self._buffer.setdefault('report', 'Compilation failure: unknown reason with code {}'.format(runcode))
                return False
        return True

    def _execute(self, runcmd, rtype=None, sin=None, sout=None, serr=None, work_path=None, tl=None, ml=None):
        command = "./sandbox/pbsbox"
        if rtype is not None:
            command = command + " --type=" + rtype
        if sin is not None:
            command = command + " --in=\"" + sin + "\""
        if sout is not None:
            command = command + " --out=\"" + sout + "\""
        if serr is not None:
            command = command + " --err=\"" + serr + "\""
        if work_path is not None:
            command = command + " --work-path=\"" + work_path + "\""
        if tl is not None:
            command = command + " --tl=" + str(tl)
        if ml is not None:
            command = command + " --ml=" + str(ml)
        command = command + " " + runcmd
        self._console('running command ' + command)
        return commands.getoutput(command).split('\n')

    def _emit_case(self, case):
        if case == 'RESET':
            self.__last_emit_case = 0
        if case == 'COMPILING':
            connection = MySQLdb.connect(kitDBHost, kitDBUsername, kitDBPassword, kitDBName)
            connection.cursor().execute("UPDATE KitStatus SET kitStatusVerdict=12 WHERE kitStatusId=" + str(self._kitRunId))
            connection.commit()
            connection.close()
            self.__socket.emit('pub', {'runid': self._kitRunId, 'case': 'COMPILING'})
            return
        if self.__last_emit_case == 0:
            self.__last_emit_stamp = time.time()
        else:
            now_time = time.time()
            if now_time - self.__last_emit_stamp <= 3.0 or case - self.__last_emit_case < 5:
                return
            self.__last_emit_stamp = now_time
        connection = MySQLdb.connect(kitDBHost, kitDBUsername, kitDBPassword, kitDBName)
        connection.cursor().execute("UPDATE KitStatus SET kitStatusVerdict=9,kitStatusExtraMessage='" + str(case) + "' WHERE kitStatusId=" + str(self._kitRunId))
        connection.commit()
        connection.close()
        self.__socket.emit('pub', {'runid': self._kitRunId, 'case': case})
        self.__last_emit_case = case

    def _consume(self, data):
        assert False
        pass

    def _console(self, message):
        self.__console('[{}] Problem {}: {}'.format(self._kitRunId, self._kitProbId, message))

    def __update_repository(self, remote_version):
        need_update = False
        if os.path.exists('probfile/version'):
            with open('probfile/version', 'r') as reader:
                local_version = reader.read().rstrip('\n')
                if not local_version.isdigit() or int(local_version) != remote_version:
                    need_update = True
        else:
            need_update = True
        if need_update:
            self.__console('Updating probfile repository...')
            try:
                if not os.path.exists('probfile'):
                    git.Git().clone(kitGitUser + "@" + kitGitHost + ":probfile")
                else:
                    git.Repo('probfile').remotes.origin.pull()
            except Exception as e:
                self.__console(e.__doc__)
                self.__console(e.message)
                traceback.print_exc()
                return
            self.__console('Repository updated.')

    def _save_code(self, data, tmpdir):
        for code in self._conf['files']:
            if 'name' not in self._conf['files'][code]:
                self._buffer.setdefault('verdict', 6)
            open(tmpdir + '/' + self._conf['files'][code]['name'], 'w').write(data[code].encode('utf8'))

    def _copy_at_stage(self, index, test, tmpdir, stage):
        for inc in test['require']:
            if test['require'][inc]['stage'] == stage:
                source = inc.replace('[$INDEX]', str(index))
                shutil.copy('probfile/' + self._kitProbId + '/' + source, tmpdir + '/' + test['require'][inc]['target'])

    def process(self, data):
        self.__console('Start consuming data, strategy={}.'.format(str(self.__class__)))
        self._kitRunId = data['runid']
        self._kitProbId = data['probid']
        self.__update_repository(data['version'])
        if not os.path.exists('probfile/' + self._kitProbId + '/problem.json'):
            self._buffer.setdefault('verdict', 6)
            self._buffer.setdefault('report', 'failed to pull the problem repository: cannot find the problem.json')
            self.__console('problem configuration JSON file not found in the repository')
        elif not os.path.exists('probfile/version'):
            self._buffer.setdefault('verdict', 6)
            self._buffer.setdefault('report', 'failed to pull the problem repository: cannot find the version')
            self.__console('version file not found in the repository')
        else:
            with open('probfile/version', 'r') as reader:
                local_version = reader.readline().rstrip('\n')
            if not local_version.isdigit() or int(local_version) != data['version']:
                self.__console(
                    'Invalid repository(local_version={}, remote_version={})'.format(local_version, data['version']))
                self._buffer.setdefault('verdict', 6)
                self._buffer.setdefault('report', 'failed to pull the problem repository: version not match')
            else:
                self._conf = json.load(open('probfile/' + self._kitProbId + '/problem.json', 'r'))
                self._console('Judge started.')
                self._consume(data)
                if os.path.exists('tmp'):
                    shutil.rmtree('tmp')
                self._console('Judge ended.')
        self._buffer.setdefault('runid', self._kitRunId)
        self._buffer.setdefault('probid', self._kitProbId)
        self._buffer.setdefault('user', data['user'])
        self.__send_report()
        self.__console('Data consumed.')

    def __periodACK(self, period):
        self.__timer = Timer(period, self.__periodACK, [period])
        self.__timer.daemon = True
        self.__connection.process_data_events()

    def start(self, period):
        self.__periodACK(period)

    def end(self):
        self.__timer.cancel()

    def __send_report(self):
        connection = pika.BlockingConnection(pika.ConnectionParameters(
            host=kitReportMQHost,
            port=kitReportMQPort,
            credentials=pika.PlainCredentials(
                username=kitReportMQUsername,
                password=kitReportMQPassword
            ),
            heartbeat_interval=kitReportMQHeartBeat
        ))
        channel = connection.channel()
        channel.queue_declare(queue=kitReportMQQueueName, durable=True)
        channel.basic_publish(
            exchange='',
            routing_key=kitReportMQQueueName,
            body=json.dumps(self._buffer),
            properties=pika.BasicProperties(delivery_mode=2)
        )
        connection.close()
