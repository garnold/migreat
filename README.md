Migreat - A simple, fast PHP-based migration framework, inspired by Rails.
==========================================================================

# Getting Started

## Basic Usage

Given a PHP script `migrate.php` containing the following 3 method calls:

    #!/usr/bin/env php
    <?php
    
    require_once '<path_to_migreat>/schema_statements.php';
    
    connect('pgsql', 'localhost', '<database>');
    migrate([
        ['1', 'Create first table',
            function () {
                execute('create table t1(c int)');
            },
            function () {
                execute('drop table t1');
            }],
        ['2', 'Create second table',
            function () {
                execute('create table t2(c int)');
            },
            function () {
                execute('drop table t2');
            }]
    ]);

* `migrate.php [UP] [STEP=<N>]` - Applies the next `N` pending migrations on `<database>` (creating it if necessary), defaulting to all.
* `migrate.php REDO [STEP=<N>]` - Reverts and reapplies the last `N` previously applied migrations, defaulting to 1.
* `migrate.php UNDO [STEP=<N>]` - Reverts the last `N` previously applied migrations, defaulting to 1.
* `migrate.php DOWN [STEP=<N>]` - Reverts the last `N` previously applied migrations, defaulting to all.

## Including in your project

### Using Peru

[Peru](https://github.com/buildinspace/peru) is a tool for including other people's code in your projects. It
fetches from anywhere -- git, hg, svn, tarballs -- and puts files
wherever you like. Peru helps you track exact versions of your
dependencies, so that your history is always reproducible. And it fits
inside your scripts and [Makefiles](https://github.com/buildinspace/peru/tree/master/docs/make_examples), so your build
stays simple and foolproof.

Peru supports Linux, Mac, and Windows. It requires **python** (3.3 or later)
and **git**, and optionally **hg** and **svn** if you want fetch from those
types of repos. Use [pip](https://pip.pypa.io/en/latest/) to install it:

    pip3 install peru

Then, create a `peru.yaml` file in the root of your project like this:

    imports:
      migreat: db/migreat
      
    git module migreat:
      url: https://github.com/garnold/migreat.git

Now run `peru sync` to pull in Migreat into your project.  See the [Peru documentation](https://github.com/buildinspace/peru) for advanced usage.

### Using Git subtrees

Migreat can be included in your project as a [Git subtree](https://git-scm.com/book/en/v1/Git-Tools-Subtree-Merging)
([tutorial](http://blogs.atlassian.com/2013/05/alternatives-to-git-submodule-git-subtree/)).  Even as late as 1.8.4,
Git will complain *subtree is not a git command*, unless you also build the `contrib/subtree` project, which is not
covered by the default root-level Makefile.  Fortunately building the subtree project is trivial.

First, download the [Git source](https://github.com/git/git/releases) matching your local Git version, then:

    unzip git-<version>.zip
    cd git-<version>/contrib/subtree
    make prefix=/usr/local/git # default location if you downloaded the OS X installer from git-scm.com
    sudo make prefix=/usr/local/git install

In order to pull in changes from the Migreat repo, you'll need to add the Migreat project as a remote:

    git remote add -f migreat git@github.com:garnold/migreat.git

Then to pull in changes:

    git fetch migreat
    git subtree pull --prefix=db/migreat migreat master --squash

To contribute changes back to upstream, simply commit those changes to your local working copy, and do:

    git subtree push --prefix=db/migreat migreat master
