import git, os, traceback, sys

try:
	os.chdir('files')
	repo = git.Repo('probfile')

	config = repo.config_writer()
	config.set_value("user", "email", "kipsora@gmail.com")
	config.set_value("user", "name", "kipsora")

	cmd = repo.git
	cmd.add('*')
	cmd.commit('-m', 'Problem changed test.', author='kipsora@gmail.com')
	print "Commited."
except Exception as e:
    print e.__doc__
    print e.message
    traceback.print_exc(sys.stdout)
    print "Failed to commit."
