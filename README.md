# php-data-objects

Data object related code for the PDGA's back-end PHP API systems.

### Install Composer Dependencies

```
./bin/composer install
```

### Run Unit Tests

```
./bin/phpunit
```

### Code Formatting Standards
#### Run PHP_CodeSniffer to see the existing styling issues.
```
composer sniff
```

#### Run the formatter to automatically fix the styling issues that can be fixed.
```
composer format
```
or 
```
composer format-verbose
```
### Require this Library as a Dependency in another Application

To add the package to an application's Composer dependencies, use the following Composer command:

```composer require pdga/data-objects```

You will also use the above command to update the package if it is already being used in your application
but the major version number has been changed.

If you want to update an existing dependency on this package and only the minor or bug fix version has been
changed you can use the following command in that application:

```composer update pdga/data-objects```

The above commands are very simple, however there are many options as far as dictating which versions are
installed that can be used. For more information see [the official Composer documentation](https://getcomposer.org/doc/articles/versions.md#writing-version-constraints).

## Branching for features and bug fixes

When creating a new feature or bug fix, you will typically start by ensuring you are on the `develop` branch and use the following
command to create your new branch:

```git checkout -b <your-branch-name>```

If there is a Jira ticket for your task then using that ticket number and title is a good candidate for your branch name
but as long as it is clear what your branch is for then you can use anything you wish.

Note that you do not always have to branch off of `develop`, for instance if you are making a change to something that isn't merged
into `develop` yet you will want to branch off of that other existing branch. It is important to remember which branch you branched
off of so that you merge back into that branch.

Once your changes are committed and pushed up, you are ready to make a Pull Request on GitHub. In your web browser, navigate to
[https://github.com/PDGA/php-data-objects/pulls](https://github.com/PDGA/php-data-objects/pulls) and click New Pull Request.
Ensure that the branch selected in the `base` dropdown is the branch you branched off of, and the option selected in the `compare`
dropdown is your branch. Click Create Pull Request and add a description of your change in the `Add a description` section.
Make sure you select at least two people as reviewers and then click `Create Pull Request` below the description box
to open the Pull Request.

## Merging a Pull Request

Once at least two people have approved your Pull Request it may be merged. There are two ways to do this:
You can either use the GUI on GitHub to automatically merge your branch into the branch you selected as `base`
and then subsequently delete your branch, or you can use the `git` command line to do the same.

### Using GitHub

The `Merge pull request` button on GitHub is at the bottom of the main Pull Request page and once clicked it
will turn into a `Delete Branch` button. After the branch is deleted you should change which branch you are working
in locally to the one you select as `base` and run the following commands:

```
git fetch -p
git pull
```
### Using git command line

To use the `git` command, first change your current working branch to your `base` branch.  Then use the following
command to merge your branch.

```git merge <your-branch-name>```

Once the merge is complete and any conflicts have been resolved and committed you must push up the `base` branch.

NOTE: Changes can not be committed and pushed directly to `develop` from the command line, so if your `base` branch was
`develop` please see the above instructions for using the GitHub GUI for merging.

At this point you can delete your branch from the origin:

```git push -d origin <your-branch-name>```

### After merging and deleting remotely

After deleting the branch from the origin, either manually or via GitHub, make sure to delete it locally:

```git branch -d <your-branch-name>```

## Releasing a new version

To release a new version of the repository, all changes need to be merged into `develop` via a Pull Request.
Changes can not be committed and pushed directly to `develop` from the command line, so a Pull Request is required.
When you intend to release a new version you will also want to update the `CHANGELOG.md` file with a list of changes
made under the new version number you intend to release (See below for information about version numbers).
Once all changes have been merged into `develop` it needs to be merged into `main`, again via a Pull Request.
Once everything is merged into `main` a new tag can be created for the repository and pushed.

### Creating a new tag

A new tag for the repository should be created using the following naming scheme:

<major-version.minor-version.bug-fix>

The major version should be incremented whenever breaking changes are added to the repository code.
The minor version should be incremented when non breaking, non bug fix changes are made to the repository code.
The bug fix version should be incremented when only bug fixes are added to the repository code.

Note that incrementing a version number sets all version number to the right of that number to 0.
For example, if a breaking change is made to version `1.4.29` you would go from `1.4.29` to `2.0.0`, NOT `2.4.29`.

To create a new tag, use the following command from the command line:

```git tag -a <new-tag-version> -m "<a comment about what this version is>"```

followed by

```git push -u origin <new-tag-number>```

For example, version 2.1.0 was tagged and released using the following commands:

```
git tag -a 2.1.0 -m "Support passing Eloquent models to `ModelInstantiator#dataObjectToDatabaseModel` for efficiency."\
git push 2.1.0
```

## Pushing a new version of the package to Packagist

This package is already set up on Packagist, which watches the repository for new tag and automatically
creates a new release version for use via Composer (The Packagist page can be found at [https://packagist.org/packages/pdga/data-objects](https://packagist.org/packages/pdga/data-objects)).
Therefore, the simple act of pushing a new tag to the repository will trigger a new version to be available in Composer.
Sometimes it can take a bit of time for the new version to become available. You can verify that the new version is available on the Packagist page.
