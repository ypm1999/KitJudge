import git, os, traceback, sys

try:
	os.chdir('files')
	repo = git.Repo('probfile')

	config = repo.config_writer()

	cmd = repo.git
	cmd.add('*')
	cmd.commit('-m', 'Problem changed test.')
	print "Commited."
except Exception as e:
    print e.__doc__
    print e.message
    traceback.print_exc(sys.stdout)
    print "Failed to commit."
