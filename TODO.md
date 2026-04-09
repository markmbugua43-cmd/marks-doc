# TreeKeeper SQL Schema Fixes - Progress Tracker

## Plan Breakdown:

1. [x] Fix email VARCHAR length in users table (160 -> 255)
2. [x] Add indexes to trees, tasks, health_checks tables for performance
3. [x] Add tree_id column + FK to tasks table
4. [x] Add tree_id column + FK to health_checks table
5. [x] Make health_checks.status ENUM consistent (add 'dead')
6. [x] Replace hardcoded user id=1 in INSERTs with subquery
7. [x] Fix future dates in sample trees INSERTs to current year
8. [ ] Test schema execution in MySQL
9. [ ] Verify PHP app compatibility (check db.php queries if needed)

**Status: Schema fixes complete. Ready for testing.**
