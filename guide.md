# Problem Configuration

## Default Problem

A problem consists of ```title```, ```source```, ```type``` and a ```JSON``` configuration file.

### Title, Source

You can specify the title, source in the problem configuration page.

### Type

You have two options in this settings: ```default``` and ```OI```

#### default
Any unaccepted datacase will interupt the judge and only return wheather accepted and the reason of unaccepted if not passed. Just like ACM-ICPC.

#### OI
Run and Judge all datas and give a final score just like NOIp and NOI.

### JSON Configuration File

A default ```JSON``` configuration file for ```A + B Problem``` is listed below:

```json
{
	"include": {
	},
	"tests": [{
		"caption": "original",
		"repeat": 10,
		"judger": {
			"type": "default",
			"path": "spjInt",
			"lang": "exec"
		},
		"compiler": {
			"C++": [
				"/usr/bin/g++ code.cpp -o code -O2 -std=c++14"
			]
		},
		"command": {
			"C++": "/usr/bin/valgrind --xml=yes --xml-file=result.xml ./code"
		},
		"type": "valgrind",
		"valgrind": "result.xml",
		"require": {
			"data/data[$INDEX].in": {
				"stage": 0,
				"target": "input.txt"
			},
			"data/data[$INDEX].out": {
				"stage": 1,
				"target": "output_std.txt"
			}
		},
		"input": "input.txt",
		"stdout": "output_std.txt",
		"output": "output.txt",
		"limit": {
			"C++": {
				"time": 1000,
				"memory": 65536
			}
		}
	}],
	"files": {
		"Code": {
			"name": "code.cpp",
			"lang": ["C++"]
		}
	},
	"main": "Code",
	"Module": {
		"Problem Statement": {
            "loader": "markdown",
            "style": "default",
            "content": "problem_statement.txt"
        },
        "Input Description": {
            "loader": "markdown",
            "style": "default",
            "content": "input_description.txt"
        },
        "Output Description": {
            "loader": "markdown",
            "style": "default",
            "content": "output_description.txt"
        },
        "Examples": {
            "style": "sample",
			"content": {
				"Input 1": {
					"path": "data/sample1.in"
				},
				"Output 1": {
					"path": "data/sample1.out"
				},
				"Input 2": {
					"path": "data/sample2.in"
				},
				"Output 2": {
					"path": "data/sample2.in"
				}
			}
        },
        "Hints": {
            "loader": "markdown",
            "style": "default",
            "content": "hints.txt"
        },
        "Limits": {
            "style": "limits"
        }
	}
}
```

And the API manual is listed below:

* ```include```: If the program needs some libraries to compile, then this field can be used to copy other libraries to the temporary working folder. For example, if you want to include a custom libraries uploaded in the problem repository folder, then you may add ```"include": ["<your library>"]``` to the configuration file.

* ```tests```: This field is used to tell the judge what the tests look like. The field ```tests``` is an array consisting of several kinds of tests, each of which is a dictionary. In this dictionary, the following keys are settled:
  * ```caption```: Indicate the name of this group of data, which can be omitted.

  * ```repeat```: Represent how many tests of this kind are there. This value may be used to substitute ```[$INDEX]``` in the ```require``` field.

  * ```judger```: Represent the special judge. This field has three subfields:

    * ```type```: Indicate the type of the special judge. This field is optional and it can be either be ```default``` or be ```custom```.
    * ```path```: Indicate the path to the special judge. If the ```type``` of the special judge is ```default``` then the path is the name of the default special judge.
    * ```lang```: Indicate the language of the judge. Currently, it can either be ```exec``` or ```C++```. If the field is not ```exec``` then the judge will be compiled firstly. But this will make a judge task time-consuming.

  * ```compiler```: Indicate the instructions of compile for different language.  
    * ATTENTION: All instructions are supposed to run in the sandbox.
      * System instruction such as ```python``` or ```g++``` should use absolute path.
	  * If some program could not run with the sandbox restrictions, you can type ```--unsafe``` before the instruction. For example 
	    ```
	    --unsafe make
	    ```
		There is no doubt that ```--unsafe``` mode may give opptunities to make damage.

  * ```command```: Indicate the run command. If you want to use valgrind to check memory, it is necessary to use ```valgrind``` instruction.
    * ATTENTION: The same to ```compiler```

  * ```type```: If you want to check memory, use ```valgrind``` in this option. Otherwise, omit it.

  * ```valgrind```: Indicate the valgrind xml file, which should match the commands.

  * ```input```: Indicate the input file.

  * ```output```: Indicate the output file.

  * ```stdout```: Indicate the standard output file.
    * ATTENTION: It is necessary to provide input, output and stdout file, even they will not be used in the judgement.

  * ```require```: Indicate the required files.
    * ```[$INDEX]```: Indicate the number of 1-based index.
	* ```stage```: ```-1``` means copy the file before compile; ```0``` means copy after compile but before run; ```1``` means copy before judge.
	  * ATTENSTION: compile, run and judge are in different directory, which means that file with stage -1 is invalid for compile and judge.
    * ```target```: Indicate the file name.

  * ```limit```: Indicate the limits of this kind of tests. The value of this field is an array, the element of which has two fields:

    * ```time```: The time limits.
    * ```memory```: The memory limits.

    And the key is the name of the language.

* ```files```: Indicate the code to be inputed or uploaded. The value of this field is an array, the element of which is a dictionary consisting of the following fields:

  * ```name```: Indicate the name of the file after being copied to the work path.
  * ```lang```: Indicate the language of the file(code).

  The key is the name of the file in the submit page.main: Indicate the key main file.

* ```main```: Indicate the key main file.

* ```Module```: This file is used to configure the problem statement page, including several sections. Each section has a title specified in the key and there are three styles of the module:

  * ```"style": "default"```: A ```default``` section is usually loaded with a markdown parser and alternative options are ```link``` which shows the ```content``` using ```<a>``` label and ```html``` which will show the content in HTML directly.
  * ```"style": "sample"```: The ```content``` field of ```sample``` section is an array consisting of several elements, the key of which is the title and the value has a field ```path``` to indicate the path to the file.
  * ```"style": "limits"```: The ```content``` field of ```limits``` section is automatically generated with the information filled in the ```tests``` section.
