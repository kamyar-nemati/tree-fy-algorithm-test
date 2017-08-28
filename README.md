# Repository name: tree-fy-algorithm-test

## Repo description
An AJAX-based web application for testing the repo 'tree-fy-algorithm'. This prototype processes multiple dependent tasks.

## Overview
User may create new __Tasks__. All tasks have __ID__, __Parent_ID__, __Name__, and __Data__ as their properties. The field data is used to store additional information. Each task might have parent task. The field Parent_ID points to the parent task. No task shall have more than one parent task. A task is independent if the field Parent_ID is equal to zero(0). The prototype supports __Status__ for each task. Task's status is stored in data field. There are states of status: __IN_PROGRESS__, __COMPLETE__, and __DONE__. All tasks are in progress once created. Any task can be marked as done. A parent task that is marked as done, it shall enter complete state until all its children marked as done. Any task that has no children may switch back to in progress state. Tasks can not be deleted.

## Features
Tasks are listed in a customized UI-Grid table. The table supports pagination, external search, and external sort. There are links for each task that triggers certain actions such as switch status, rename, and change parent task. All tasks are demonstrated in hierarchical representation as well.
