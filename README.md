# Menu changes and GitHub

Menu additions, edits, and deletions are recorded in `Database/menu_setup.sql`.
Uploaded menu photos are stored in `Assets/images/menu/`.

After making menu changes, include the updated SQL file and any changed photo
files in your Git commit. After your friend pulls the commit, they must import
`Database/menu_setup.sql` into their `restaurant_db` database in phpMyAdmin to
apply those menu changes. GitHub stores the changes but does not update a
computer's local MySQL database automatically.