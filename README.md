# Repository name: tree-fy-algorithm-test

## Repo Description
An AJAX-based web application for testing the repo 'tree-fy-algorithm'. This prototype processes multiple dependent tasks.

## Dependency
This repository depends on [tree-fy-algorithm](https://github.com/kamyar-nemati/tree-fy-algorithm) and expects to find __Node.php__ and __Tree.php__ classes under _'application/core/tree-fy-algorithm/'_.

To pull down the dependency repo, navigate to the project's root directory and type:
```
git submodule init
git submodule update
```

## Requirements Overview
User may create new Tasks. All tasks have ID, ParentID, Name, and Data as their properties. The field data is used to store additional information and it can be NULL. Each task might have a parent task. The field ParentID points to the parent task. No task shall have more than one parent task. A task is independent if the field ParentID is equal to zero(0). The prototype supports status for each task. Task's status is stored in the Data field. There are three(3) states of status: IN_PROGRESS, COMPLETE, and DONE. All tasks are in progress once created. Any task can be marked as done. A parent task that is marked as done, it shall enter the complete state until all its children marked as done. Any task that has no children may switch back to in progress state. Tasks can not be deleted.

## Prototype Features
Tasks are listed in a customized UI-Grid table. The table supports pagination, external search, and external sort. There are links for each task that triggers certain actions such as switch status, rename, and change parent task. All tasks are demonstrated in hierarchical representation as well.

![Hierarchical Representation](https://user-images.githubusercontent.com/29518086/29768228-13670ab6-8c18-11e7-9ab3-f34e1272a966.png "Hierarchical Representation")

## Database
You need to have PostgreSQL installed. The prototype will create the required table. However, you need to have a database and an authorized role to login.

Instructions below might be useful.
* `CREATE DATABASE task_tree ;`
* `CREATE ROLE taskadmin WITH PASSWORD 'abc123' ;`
* `ALTER ROLE taskadmin WITH LOGIN ;`
