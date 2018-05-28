#!/bin/sh
BASE=`dirname $0`'/../';

echo $BASE

PROJECT=project_manager_2017
HOST=kona.teleapth
USER=telepath

REV=`git log --oneline | wc -l`
REV=`echo $REV | tr -d " "`
echo revision $REV

REPOS=https://github.com/yoo16/project-manager
PROJECT_REV=${PROJECT}_g$REV

cd ${BASE}tmp
git clone $REPOS $PROJECT_REV

rm -r ../tmp/$PROJECT_REV/.git
rm -r ../tmp/$PROJECT_REV/.gitignore
find ../tmp/$PROJECT_REV/ -type d -exec chmod 775 {} +
tar jcf $PROJECT_REV.tar.bz2 $PROJECT_REV && \
rm -rf $PROJECT_REV && \
scp $PROJECT_REV.tar.bz2 $USER@$HOST:~/projects/$PROJECT/versions/archives/
