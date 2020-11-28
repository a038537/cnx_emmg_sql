#include <mysql.h>
#include <stdio.h>
#include <stdlib.h>
#include <stdint.h>

uint8_t systemkey[16];
uint8_t key20[16];
uint8_t key21[16];
uint8_t debug =1;

void finish_with_error(MYSQL *con)
{
  fprintf(stderr, "%s\n", mysql_error(con));
  mysql_close(con);
  exit(1);
}

int main(int argc, char **argv)
{
   MYSQL *con = mysql_init(NULL);
   MYSQL_ROW row;
   MYSQL_RES *result;
   int num_fields;

  if (con == NULL)
  {
      fprintf(stderr, "mysql_init() failed\n");
      exit(1);
  }

  if (mysql_real_connect(con, "localhost", "root", "gftty2478",
          "neovision", 0, NULL, 0) == NULL)
  {
      finish_with_error(con);
  }

    /* SEND UNIQUE EMM TO ALL CARDS IN SUBSCRIPTION */
    if (mysql_query(con, " \
                SELECT concat('U:',abo.ppua), concat('S:',abo.`start-date`) , \
                  concat('E:',abo.`stop-date`), concat('A:',abo.access), \
                  concat('N:',providers.providername), concat('SA:',cards.ppsa), concat('ID:',providers.chid)\
                FROM neovision.providers join neovision.abo join neovision.cards \
                ON providers.chid = abo.chid and cards.ppua = abo.ppua and cards.deleted = 0;\
                "))
    {
        finish_with_error(con);
    }

  result = mysql_store_result(con);

  if (result == NULL){
      finish_with_error(con);
  }

  num_fields = mysql_num_fields(result);

    while ((row = mysql_fetch_row(result))){
        for(int i = 0; i < num_fields; i++){
            printf("%s ", row[i] ? row[i] : "NULL");
        }
        printf("\n");
    }

    /* SEND SHARED EMM TO ALL CARDS IN SUBSCRIPTION */
    if (mysql_query(con, "SELECT DISTINCT concat('G:',ppsa) FROM neovision.cards JOIN neovision.abo WHERE cards.deleted = 0 AND cards.ppua = abo.ppua;"))
    {
        finish_with_error(con);
    }

    result = mysql_store_result(con);
    if (result == NULL){finish_with_error(con);}
    num_fields = mysql_num_fields(result);

    while ((row = mysql_fetch_row(result))){
        for(int i = 0; i < num_fields; i++){
            printf("%s ", row[i] ? row[i] : "NULL");
        }
    printf("\n");
    }

    /* DELETE OLD SUBSCRIPTIONS */
    if (mysql_query(con, "DELETE FROM neovision.abo WHERE abo.`stop-date` < (NOW() - INTERVAL 1 MONTH);"))
    {
        finish_with_error(con);
    }

    mysql_free_result(result);
    mysql_close(con);

    exit(0);
}
