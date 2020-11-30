#ifndef PARSER_H
#define PARSER_H
#include <stdint.h>

extern void setup(int runde,char *sendbuffer,uint8_t debug);
extern void genframe(char *sendbuffer);
extern void copyemm(char *out,uint8_t *in);

#endif // PARSER_H



