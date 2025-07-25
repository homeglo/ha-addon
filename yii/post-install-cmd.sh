#!/bin/sh
if [ -n "$DYNO" ]  && [ -n "$ENV" ]; then
    #php init --env=$ENV --overwrite=All
    php yii migrate --migrationPath=@yii/log/migrations/ --interactive=0
    php yii migrate --migrationPath=@yii/rbac/migrations --interactive=0
    php yii migrate/up --interactive=0
    echo "Post scripts executed!"

    php yii cache/flush-all
    php yii cache/flush-schema --interactive=0
fi