![GitHub release (with filter)](https://img.shields.io/github/v/release/janson-git/release-builder)
![GitHub Release Date - Published_At](https://img.shields.io/github/release-date/janson-git/release-builder)
![GitHub repo size](https://img.shields.io/github/repo-size/janson-git/release-builder)
![GitHub Top Language](https://img.shields.io/github/languages/top/janson-git/release-builder)

# Release Builder 
**Release Branches Manage Tool**

### About
This is a tool to make release branches management simple and convenient.

### Requires
- Git
- Docker and docker-compose

[Terms used in this app](./docs/terms.md)

### Get Started

I recommend start with this document:
[How to start and workflow example](./docs/example_flow_with_public_repo.md)

All information below is based on it and possible to read after to extend knowledge of app.

-----

#### INSTALL (you need docker and docker-compose)

1. Clone, install and start it
```shell
git clone https://github.com/janson-git/release-builder.git
cd release-builder
make install
make up
```

or you can make this manually:
```shell
git clone https://github.com/janson-git/release-builder.git
cd release-builder
cp .env.example .env
docker-compose build
docker-compose up -d
```

2. Open http://localhost:9088/ in your browser. Register as new user in your  `Release Builder` app
3. Token. If you plan work with GitHub repositories only, I recommend you create Personal Access Token (PAT) on GitHub. Classical and fine-grained tokens available to use. 
4. If you work with other repositories then it would be more suitable to create personal ssh key. That key will use in interactions with repositories from `Release Builder` app.
   I recommend DO NOT USE your personal SSH key for that, but create new one for `Release Builder`.
   - create ssh keys with `ssh-keygen` command
   - upload `.pub` key part to your GitHub (or other repository storage) account
   - put you private key part to your `Release Builder` app account on page  http://localhost:9088/user (click `Add SSH key` button)
4. it shows on user profile page if SSH key already uploaded to app


#### CREATE NEW PROJECT

Ok, app prepared, and we need add our first project to work with it
1. **variant 1 (UI):** Add new repository which needed to create your project: 
   - open UI on `Git` page (http://localhost:9088/git)
   - click `Add repository` button
   - Now just write URL (`https://github.com/janson-git/release-builder.git`) of your repository to input field and press the `Save` button 
2. **OR variant 2 (terminal):** Go to `storage/repos` directory of your `Release Builder` app. This directory will contain local repositories of our projects. Clone repository that you want to use with `Release Builder`. Something like this:
   ```shell
   cd storage/repos
   git clone https://github.com/janson-git/release-builder.git
   ```
3. If previous step finished successfully, we can create new project for this repo form UI.
4. Open in browser http://localhost:9088/projects and click `Create new project`
5. Find your cloned project directory in directory navigator. Navigate by click on folder names.
6. When you found it (you will see branches list on that page), mark folder with checkbox and click on `Build Project`
7. Right after that click `Save Project`


#### CREATE PACK IN PROJECT

Congratulations!
It is time to create our first release branch!


`Release Builder` works with PACKS and BUILDS.
PACK - it is just kind of plan: branches list that you want to merge in release branch.
BUILD - result of merging PACK branches to one branch. In fact `build` - it is release branch.


You need additionally set your name and email which will showed as commit author on release branches created in `Release Builder`.

Go to `Profile` page and press `Set commit author` button to fill this information. This information will display on `Git` log as commit author of release branch.

If you skip this step then default value will use. It contains in `.gitconfig` file in project root directory contains username and email.

Default content of .gitconfig is:
```shell
[user]
name = "Release Builder"
email = "release-builder@local"
```

This commit author info used in your `Release Builder` app by users who didn't set personal commit author fields!


1. Let's create first pack! Click on `Create new pack` button on your Project page
2. Now you need to set release branch name, let's put `release-01` to pack name field
3. Mark with checkboxes all branches that you want to add to your pack, and click `Save pack` button
   Now app will fetch repository and create new local release branch with name like `build-release_01-20230331-214701`
   But it is still empty branch (equal to `master` or `main`) without pack branches right now
5. Click `Merge branches` button to start merging process. If CONFLICT happens - read `RESOLVING MERGE CONFLICTS` doc below
6. When all is ok, you can push release branch to repository. Just click on `Push build to repository` button
7. Check remote repository, and you will see new release branch there!

##### Note!

When you need update _release_ branch with changes from working branches, just click on`Fetch repositories` to get changes and click `Merge branches` to update your _release_ branch.
After that - just send this renewed _release_ branch to repository by click on `Push build to repository` 


#### RESOLVING MERGE CONFLICTS
You need resolve conflicts by creating merge-branches and add them to pack.

1. If you get error on merge and see `CONLICT` in logs then return to pack page and click `Search conflict branches` button
2. After short or long time you will see something like this:
```
task-xxx: ok
#1: TROUBLE: task-yyy TO master
MERGE_BRANCH: merge-0331-task-yyy-to-master
DESC: Auto-merging run.php
CONFLICT (content): Merge conflict in run.php
Automatic merge failed; fix conflicts and then commit the result.
DIFF: diff --cc run.php
...
...
```

What you can see here?

- conflict on merging `task-yyy` branch to `master` branch
- recommended name of merge-branch `merge-0331-task-yyy-to-master`
- DIFF is showed for details

Now you need to use portion of git kung-fu:
1. create new merge-branch based on `master` (I recommend to use name from conflict descriptions: in this case you will see what branches is conflicted and merged for each merge-branch)
2. merge `task-yyy` branch to that new merge-branch, you need to resolve conflicts on this step
3. commit and push merge-branch to repository
4. return to Pack page, click `Add branches` button, find your merge-branch in list and accept it to pack
5. then on pack page click `Remove build` and after that - click `Merge branches` again
6. Now it must be ok. If not - ask me
7. After all is ok, you have good state release build which can be pushed to repository


### Security

`Release Builder` has authorisation, but it mainly "identification". This tool developed for in-house usage.
Registration are completely open, user password hashes are available through, inner database management tool.
On project start it didn't require a fully-protected access and was created quickly and simple.
It enables to switch-on Basic Auth in .env to protect the access.
Please secure access to `Release Builder` by your way.
