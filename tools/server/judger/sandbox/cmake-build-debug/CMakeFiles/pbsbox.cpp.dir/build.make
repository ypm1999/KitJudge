# CMAKE generated file: DO NOT EDIT!
# Generated by "Unix Makefiles" Generator, CMake Version 3.7

# Delete rule output on recipe failure.
.DELETE_ON_ERROR:


#=============================================================================
# Special targets provided by cmake.

# Disable implicit rules so canonical targets will work.
.SUFFIXES:


# Remove some rules from gmake that .SUFFIXES does not remove.
SUFFIXES =

.SUFFIXES: .hpux_make_needs_suffix_list


# Suppress display of executed commands.
$(VERBOSE).SILENT:


# A target that is always out of date.
cmake_force:

.PHONY : cmake_force

#=============================================================================
# Set environment variables for the build.

# The shell in which to execute make rules.
SHELL = /bin/sh

# The CMake executable.
CMAKE_COMMAND = /home/kipsora/.local/share/JetBrains/Toolbox/apps/CLion/ch-0/171.4694.31/bin/cmake/bin/cmake

# The command to remove a file.
RM = /home/kipsora/.local/share/JetBrains/Toolbox/apps/CLion/ch-0/171.4694.31/bin/cmake/bin/cmake -E remove -f

# Escaping for special characters.
EQUALS = =

# The top-level source directory on which CMake was run.
CMAKE_SOURCE_DIR = /var/www/html/KitJudge/tools/server/judger/Strategy/sandbox

# The top-level build directory on which CMake was run.
CMAKE_BINARY_DIR = /var/www/html/KitJudge/tools/server/judger/Strategy/sandbox/cmake-build-debug

# Include any dependencies generated for this target.
include CMakeFiles/pbsbox.cpp.dir/depend.make

# Include the progress variables for this target.
include CMakeFiles/pbsbox.cpp.dir/progress.make

# Include the compile flags for this target's objects.
include CMakeFiles/pbsbox.cpp.dir/flags.make

CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.o: CMakeFiles/pbsbox.cpp.dir/flags.make
CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.o: ../pbsbox.cpp
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green --progress-dir=/var/www/html/KitJudge/tools/server/judger/Strategy/sandbox/cmake-build-debug/CMakeFiles --progress-num=$(CMAKE_PROGRESS_1) "Building CXX object CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.o"
	/usr/bin/c++   $(CXX_DEFINES) $(CXX_INCLUDES) $(CXX_FLAGS) -o CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.o -c /var/www/html/KitJudge/tools/server/judger/Strategy/sandbox/pbsbox.cpp

CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.i: cmake_force
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green "Preprocessing CXX source to CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.i"
	/usr/bin/c++  $(CXX_DEFINES) $(CXX_INCLUDES) $(CXX_FLAGS) -E /var/www/html/KitJudge/tools/server/judger/Strategy/sandbox/pbsbox.cpp > CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.i

CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.s: cmake_force
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green "Compiling CXX source to assembly CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.s"
	/usr/bin/c++  $(CXX_DEFINES) $(CXX_INCLUDES) $(CXX_FLAGS) -S /var/www/html/KitJudge/tools/server/judger/Strategy/sandbox/pbsbox.cpp -o CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.s

CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.o.requires:

.PHONY : CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.o.requires

CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.o.provides: CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.o.requires
	$(MAKE) -f CMakeFiles/pbsbox.cpp.dir/build.make CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.o.provides.build
.PHONY : CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.o.provides

CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.o.provides.build: CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.o


# Object files for target pbsbox.cpp
pbsbox_cpp_OBJECTS = \
"CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.o"

# External object files for target pbsbox.cpp
pbsbox_cpp_EXTERNAL_OBJECTS =

pbsbox.cpp: CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.o
pbsbox.cpp: CMakeFiles/pbsbox.cpp.dir/build.make
pbsbox.cpp: CMakeFiles/pbsbox.cpp.dir/link.txt
	@$(CMAKE_COMMAND) -E cmake_echo_color --switch=$(COLOR) --green --bold --progress-dir=/var/www/html/KitJudge/tools/server/judger/Strategy/sandbox/cmake-build-debug/CMakeFiles --progress-num=$(CMAKE_PROGRESS_2) "Linking CXX executable pbsbox.cpp"
	$(CMAKE_COMMAND) -E cmake_link_script CMakeFiles/pbsbox.cpp.dir/link.txt --verbose=$(VERBOSE)

# Rule to build all files generated by this target.
CMakeFiles/pbsbox.cpp.dir/build: pbsbox.cpp

.PHONY : CMakeFiles/pbsbox.cpp.dir/build

CMakeFiles/pbsbox.cpp.dir/requires: CMakeFiles/pbsbox.cpp.dir/pbsbox.cpp.o.requires

.PHONY : CMakeFiles/pbsbox.cpp.dir/requires

CMakeFiles/pbsbox.cpp.dir/clean:
	$(CMAKE_COMMAND) -P CMakeFiles/pbsbox.cpp.dir/cmake_clean.cmake
.PHONY : CMakeFiles/pbsbox.cpp.dir/clean

CMakeFiles/pbsbox.cpp.dir/depend:
	cd /var/www/html/KitJudge/tools/server/judger/Strategy/sandbox/cmake-build-debug && $(CMAKE_COMMAND) -E cmake_depends "Unix Makefiles" /var/www/html/KitJudge/tools/server/judger/Strategy/sandbox /var/www/html/KitJudge/tools/server/judger/Strategy/sandbox /var/www/html/KitJudge/tools/server/judger/Strategy/sandbox/cmake-build-debug /var/www/html/KitJudge/tools/server/judger/Strategy/sandbox/cmake-build-debug /var/www/html/KitJudge/tools/server/judger/Strategy/sandbox/cmake-build-debug/CMakeFiles/pbsbox.cpp.dir/DependInfo.cmake --color=$(COLOR)
.PHONY : CMakeFiles/pbsbox.cpp.dir/depend

