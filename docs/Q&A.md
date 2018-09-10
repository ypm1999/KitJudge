# Q&A
### Q: Wheather can I config interactive problem?
A: Yes, of course, but maybe a little difficult. First, you are supposed to code a interact script as a independent program in any language you like such as ```C++``` or ```Python``` etc. Then, try to run the script in the sandbox, that is type run command in the ```command``` API. Make sure the script will output the result and the score(if is necessary) and transform to the final result of ```KitJudge``` by a spcific special judge.   
We recommond that use ```--unsafe``` mode for there may be some system calls in the interact script which may be banned in the sandbox.

### Q: Wheather can I socre according to time or memory used?
A: Yes, of course. There are two ways. The first way is using interact script to judge the program. Second, if you have provide part of the code, such as some ```hpp``` or ```cpp```, it is avilible to print the time to consle and secial judge may judge the output.

### Q: Wheather can it used in exams and ordinary homework replacing the SJTU OJ?
A: As you wish. Basauce we have not done the stress testing and reliability testing, be cautious to use in exams.
