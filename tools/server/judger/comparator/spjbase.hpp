#ifndef SPJ_BASE
#define SPJ_BASE

#include <string>
#include <iostream>

#define SPJ_OK 0
#define SPJ_WA 1
#define SPJ_STD_IO_ERROR 2
#define SPJ_SRC_IO_ERROR 3

struct SPJException{
	std::string message;
	int exitcode;
	SPJException() {}
	SPJException(const std::string &message, int exitcode) : message(message), exitcode(exitcode) {}
	void print() {
		std::cout << message << std::endl;
	}
};

long getFileSize(FILE *stream, int exitcode) {
	long curpos, ret;
	if ((curpos = ftell(stream)) < 0) {
		throw SPJException("Failed to read the file.", exitcode);
	}
	if (fseek(stream, 0L, SEEK_END) < 0) {
		throw SPJException("Failed to read the file.", exitcode);
	}
	if ((ret = ftell(stream)) < 0) {
		throw SPJException("Failed to read the file.", exitcode);
	}
	if (fseek(stream, 0L, curpos) < 0) {
		throw SPJException("Failed to read the file.", exitcode);
	}
	return ret;
}

#endif
