// Client side C/C++ program to demonstrate Socket programming
#include <stdio.h>
#include <sys/socket.h>
#include <arpa/inet.h>
#include <unistd.h>
#include <string.h>
#include <stdint.h>
#include <mysql.h>
#include "parser.h"
#include "conax_core.h"


int i;
int timer;
uint8_t frame[255];
uint8_t tbuffer[64];
bool fileopen = 0;
bool fileerr = 0;
FILE *fp = NULL;
char temm[1024] = {0};
uint8_t debug = 0;

void finish_with_errors(MYSQL *con){
  fprintf(stderr, "%s\n", mysql_error(con));
  mysql_close(con);
  exit(1);
}

int main(int argc, char const *argv[])
{
    unsigned int port =0;
	const char *ip = "127.0.0.1";
	const char *database = "127.0.0.1";
	const char *dbname = "neovision";
	const char *user = "admin";
	const char *pass = "password";
    int sock = 0, BytesSent = 0;
    struct sockaddr_in serv_addr;
    char buffer[1024] = {0};
    char sendbuf[1024];

    MYSQL *con = mysql_init(NULL);
    MYSQL_ROW row;
    MYSQL_RES *result;
    int num_fields;

    if (con == NULL){
      fprintf(stderr, "mysql_init() failed\n");
      exit(1);
    }

     if(argc < 2){
        fprintf(stderr,"Error! No args defined! Exiting...\n");
        fprintf(stderr,"Usage: conax-emmg --port [val] --ip [val] [--debug]\n");
        return -1;
     }

     for(int i = 1;i<argc;i++){
        if(!strcmp(argv[i],"--muxport")){
            port=strtoul(argv[i+1],NULL,0);
        }
        if(!strcmp(argv[i],"--muxip")){
            ip=argv[i+1];
        }
        if(!strcmp(argv[i],"--gubed")){
            debug = 1;
        }
        if(!strcmp(argv[i],"--dbip")){
            database=argv[i+1];
        }
        if(!strcmp(argv[i],"--dbuser")){
            user=argv[i+1];
        }
        if(!strcmp(argv[i],"--dbpass")){
            pass=argv[i+1];
        }
        if(!strcmp(argv[i],"--dbname")){
            dbname=argv[i+1];
        }
        if(!strcmp(argv[i],"--help")){
            printf("usage: conax-emmg --port 1234 --ip 192.168.1.1 --dbip 192.168.1.1  --dbuser admin --dbpass password\n\n");
            printf("ARGS:\n\t--muxip\t\tIP-ADDRESS of MUX\n");
            printf("\t--muxport\t\tPORT of MUX\n");
            printf("\t--dbip\t\tIP-ADDRESS of SQL-Database\n");
            printf("\t--dbuser\tDATABASE Username [default: admin]\n");
            printf("\t--dbpass\tDATABASE Password [default: password]\n");
            printf("\t--dbname\tNAME of CAS-TABLE in Database [default: neovision]\n");
        }
     }

    if(port == 0){
        fprintf(stderr,"Error! No Port defined! Exiting...\n");
        return -1;
    }

	read_syskey_sql(debug,database,user,pass,dbname);
	read_keys_sql(debug,database,user,pass,dbname);

    if ((sock = socket(AF_INET, SOCK_STREAM, 0)) < 0)
    {
        printf("\n Socket creation error \n");
        return -1;
    }

    serv_addr.sin_family = AF_INET;
    serv_addr.sin_port = htons(port);

    // Convert IPv4 and IPv6 addresses from text to binary form
    if(inet_pton(AF_INET, ip, &serv_addr.sin_addr)<=0)
    {
        printf("\nInvalid address/ Address not supported \n");
        return -1;
    }

    if (connect(sock, (struct sockaddr *)&serv_addr, sizeof(serv_addr)) < 0)
    {
        printf("\nERROR! Connection to MUX failed...\n");
        return -1;
    }

     setup(0,sendbuf,debug);
     send(sock, sendbuf, ((sendbuf[4]+5) &0xff ), 0);
     recv(sock , buffer, 1024,0);
	if(debug == 1){
		printf("\n\n\n");
		printf("Incoming Message\n");
		for(i =0;i < buffer[4]+5;i++){printf("%02X ",buffer[i]&0xff);}
		printf("\n");
	}
     setup(1,sendbuf,debug);
     send(sock, sendbuf, ((sendbuf[4]+5) &0xff ), 0);
     recv(sock , buffer, 1024,0);
	 if(debug == 1){
		printf("\n");
		printf("Incoming Message\n");
		for(i =0;i < buffer[4]+5;i++){printf("%02X ",buffer[i]&0xff);}
		printf("\n");
	 }
     setup(2,sendbuf,debug);
     send(sock, sendbuf, ((sendbuf[4]+5) &0xff ), 0);
     recv(sock, buffer, 1024,0);
	 if(debug == 1){
		printf("\n");
		printf("Incoming Message\n");
		for(i =0;i < buffer[4]+5;i++){printf("%02X ",buffer[i]&0xff);}
		printf("\n\n\n");
	 }

    /* CONNECT TO DB */
    if (mysql_real_connect(con, database, user, pass, dbname, 0, NULL, 0) == NULL){
      finish_with_errors(con);
    }

while(true){

    if(timer <= time(0)){

    /* SEND UNIQUE EMM TO ALL CARDS IN SUBSCRIPTION */
    if (mysql_query(con, " \
                SELECT concat('U:',abo.ppua,' '), concat('S:',abo.`bos`,' ') , \
                  concat('E:',abo.`eos`,' '), concat('A:',abo.acc,' '), \
                  concat('N:',providers.providername,' '), concat('SA:',cards.ppsa,' '), concat('ID:',providers.chid,' ')\
                FROM neovision.providers join neovision.abo join neovision.cards \
                ON providers.chid = abo.chid and cards.ppua = abo.ppua and cards.deleted = 0;\
                "))
    {
        finish_with_errors(con);
    }

  result = mysql_store_result(con);

  if (result == NULL){
      finish_with_errors(con);
  }

  num_fields = mysql_num_fields(result);

    while ((row = mysql_fetch_row(result))){
        for(int i = 0; i < num_fields; i++){
            //printf("%s ", row[i] ? row[i] : "NULL");
            strcat(temm,row[i]);
        }
        //printf("%s\n",temm);
        generate_emm(temm,tbuffer,debug);
        genframe(sendbuf);
        copyemm(sendbuf,tbuffer);
        if(debug == 1){
			for(int i =0;i < 0xcf;i++){printf("%02X ",sendbuf[i]&0xff);}
			printf("\n");
		}
        BytesSent = send(sock, sendbuf, ((sendbuf[4]+5) &0xff ), 0);

		if(BytesSent == SO_ERROR){
			printf("Client: send() error.\n");
		} else {
			if(debug == 1){
				printf("Client: send() is OK - bytes sent: %d\n\n\n", BytesSent);
			}
			}
			usleep(50000);
            memset(temm,0,1023);
            //mysql_free_result(result);

	} /* END SEND UNIQUE EMM */

	/* SEND SHARED EMM TO ALL CARDS IN SUBSCRIPTION */
    if (mysql_query(con, "SELECT DISTINCT concat('G:',ppsa) FROM neovision.cards JOIN neovision.abo WHERE cards.deleted = 0 AND cards.ppua = abo.ppua;"))
    {
        finish_with_errors(con);
    }

    result = mysql_store_result(con);
    if (result == NULL){finish_with_errors(con);}
    num_fields = mysql_num_fields(result);

    while ((row = mysql_fetch_row(result))){
        for(int i = 0; i < num_fields; i++){
            //printf("%s ", row[i] ? row[i] : "NULL");
            strcat(temm,row[i]);
        }
        //printf("%s\n",temm);
        generate_emm(temm,tbuffer,debug);
        genframe(sendbuf);
        copyemm(sendbuf,tbuffer);
        if(debug == 1){
			for(int i =0;i < 0xcf;i++){printf("%02X ",sendbuf[i]&0xff);}
			printf("\n");
		}
        BytesSent = send(sock, sendbuf, ((sendbuf[4]+5) &0xff ), 0);

		if(BytesSent == SO_ERROR){
			printf("Client: send() error.\n");
		} else {
			if(debug == 1){
				printf("Client: send() is OK - bytes sent: %d\n\n\n", BytesSent);
			}
			}
			usleep(50000);
            memset(temm,0,1023);
            //mysql_free_result(result);

	} /* END SEND SHARED EMM */

    /* DELETE OLD SUBSCRIPTIONS */
    if (mysql_query(con, "DELETE FROM neovision.abo WHERE abo.`eos` < (NOW() - INTERVAL 2 MONTH);"))
    {
        finish_with_errors(con);
    }

    /* UPDATE ECM-KEYS */
    if (mysql_query(con, "\
                    UPDATE neovision.ecmg_keys \
                    SET ecmg_keys.ecmkey = md5(rand()*1001), ecmg_keys.modified = (NOW() + INTERVAL 1 MONTH) \
                    WHERE ecmg_keys.modified < (NOW() - INTERVAL 1 MONTH); \
                    "))
    {
        finish_with_errors(con);
    }

    read_keys_sql(debug,database,user,pass,dbname);

		if(debug == 1){
			printf("Database processed... Continue sending NULL-Packets\n");
		}
		timer = (time(0)+120);
        mysql_free_result(result);
     //end while rows
    }

    usleep(50000);
}
    mysql_free_result(result);
    mysql_close(con);
    return 0;
}
