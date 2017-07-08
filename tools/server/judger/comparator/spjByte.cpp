#include <cstdio>
#include <cstring>
#include <algorithm>
#include "spjbase.hpp"

int main(int argc, char *argv[]) {
	if (argc < 3) {
		printf("spjByte: A tool to compare file byte by byte\n");
		printf("Usage: spjByte [stdfile] [srcfile]\n");
	} else {
		try{
			FILE *instd = fopen(argv[1], "r");
			FILE *insrc = fopen(argv[2], "r");
			if (instd == NULL) {
				throw SPJException("Cannot open the stdfile.", SPJ_STD_IO_ERROR);
			}
			if (insrc == NULL) {
				throw SPJException("Cannot open the srcfile.", SPJ_SRC_IO_ERROR);
			}
			int size = 0;
			while (true) {
				char stdans = fgetc(instd);
				char srcans = fgetc(insrc);
				if ((stdans == EOF) ^ (srcans == EOF)) {
					printf("The stdfile's size differs from srcfile.");
					fclose(instd);
					fclose(insrc);
					return SPJ_WA;
				}
				if (stdans == EOF && srcans == EOF) {
					break;
				}
				if (stdans == '\r') stdans = '\n';
				if (srcans == '\r') srcans = '\n';
				size++;
				if (stdans != srcans) {
					if (size % 20 == 1) {
						printf("Read \"%c\", but expects \"%c\" at the %d-st token.\n", srcans, stdans, size);
					} else if (size % 20 == 2) {
						printf("Read \"%c\", but expects \"%c\" at the %d-nd token.\n", srcans, stdans, size);
					} else {
						printf("Read \"%c\", but expects \"%c\" at the %d-th token.\n", srcans, stdans, size);
					}
					fclose(instd);
					fclose(insrc);
					putScore(argc, argv, 0.0, SPJ_SCR_IO_ERROR);
					return SPJ_WA;
				}
			}
			printf("OK, %d tokens.\n", size);
			fclose(instd);
			fclose(insrc);
			putScore(argc, argv, getScore(argc, argv, SPJ_SCR_IO_ERROR), SPJ_SCR_IO_ERROR);
			return SPJ_OK;
		} catch(SPJException e) {
			e.print();
			return e.exitcode;
		}
	}
	return 0;
}
