#define THREADS 5
#define BUFSIZE 512

typedef struct target_struct{
  char host[80];
  char oid[256];
  char community[80];
  char rrd[256];
  struct target_struct *next;
  struct target_struct *prev;
}target_t;

typedef struct thread_struct{
    int index;
    pthread_t thread;
    struct threads_struct *threads;
}thread_t;

typedef struct threads_struct{
    int work_count;
    thread_t member[THREADS];
    pthread_mutex_t mutex;
    pthread_cond_t done;
    pthread_cond_t work;
}threads_t;

void *poller(void *thread_args);
void *sig_handler();
unsigned long long int snmp_get(char *snmp_host, char *snmp_comm, char *snmp_oid, int who);
int lock;
int sighup;

