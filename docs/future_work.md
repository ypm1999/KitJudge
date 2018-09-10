# Future Work
## Recode the sandbox
For now, the sandbox is baesd on ```ptrace``` technique, which is outdated. The sandbox catch each system call and check weather it may cause damage. Unfortunately, this may ban some harmless syscall. The same technique is used in ```SJTU OJ```, that's why it could not run python until today. However, ```docker``` may solve this problem. The judge core of [longint](www.longint.org) is used this technique.

## Optimize synchronous of problem config
Now, there is a version tag to check the data version. If a newer version is found, copy all data by ```cp``` instruction. Maybe there is some tools for such work.

## Simplify problem config
Maybe it is a good way to config simple problem in GUI.

## Beautify UI

