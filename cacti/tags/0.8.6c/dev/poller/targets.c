#include "inc.h"

int get_targets(){
  char query[256];
  extern target_t *targets;
  extern conf_t conf;
  target_t *temp;
  target_t *temp2;
  target_t *head;
  int target_id=0;
  MYSQL mysql;
  MYSQL_RES *result;
  MYSQL_ROW row;
  mysql_init(&mysql);
  if (!mysql_real_connect(&mysql, conf.sqlhost, conf.sqluser, conf.sqlpw, conf.sqldb, 0, NULL, 0)){
    fprintf(stderr, "%s\n", mysql_error(&mysql));
    exit(1);
  }
  sprintf(query, "select action,command,management_ip,snmp_community, \
    snmp_version, snmp_username, snmp_password, rrd_name, rrd_path, \
    arg1, arg2, arg3, local_data_id from data_input_data_cache order \
    by local_data_id");
  if (mysql_query(&mysql, query)) fprintf(stderr, "Error in query\n");
  if ((result = mysql_store_result(&mysql)) == NULL){
    fprintf(stderr, "Error retrieving data\n");
    exit(1);
  }
  mysql_close(&mysql);
  free(targets);
  targets=NULL;
  while ((row = mysql_fetch_row(result))) {
    temp = (target_t *) malloc(sizeof(target_t));

    temp->target_id = target_id;
    temp->action = atoi(row[0]);
    sprintf(temp->command, "%s", row[1]);
    sprintf(temp->management_ip, "%s", row[2]);
    sprintf(temp->snmp_community, "%s", row[3]);
    temp->snmp_version = atoi(row[4]);
    //not used at the moment
    sprintf(temp->snmp_username, "%s", row[5]);
    //not used at the moment
    sprintf(temp->snmp_password, "%s", row[6]);
    sprintf(temp->rrd_name, "%s", row[7]);
    sprintf(temp->rrd_path, "%s", row[8]);
    sprintf(temp->arg1, "%s", row[9]);
    sprintf(temp->arg2, "%s", row[10]);
    sprintf(temp->arg3, "%s", row[11]);
    temp->local_data_id = atoi(row[12]);

    temp->prev=NULL;
    temp->next=NULL;
    temp->head=NULL;
    if(targets == NULL){
      targets = temp;
      head = temp;
    }else{
      for(temp2 = targets; temp2->next !=NULL; temp2 = temp2->next);
      temp->prev = temp2;
      temp->head = head;
      temp2->next = temp;
    }
  target_id++;
  }
  temp=NULL;
  free(temp);
  temp2=NULL;
  free(temp2);
  return (int)mysql_num_rows(result);
}
