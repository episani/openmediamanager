#include <stdlib.h>
#include <sys/types.h>
#include <unistd.h>
#include <stdio.h>

int main()
{

   setuid (0);

    /* WARNING: Only use an absolute path to the script to execute,
     *          a malicious user might fool the binary and execute
     *          arbitary commands if not.
     * */

   system ("/bin/sh /var/www/openmediamanager/commands/shutdown.sh");

   return 0;
}
