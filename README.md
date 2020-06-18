# The Workday Minnesota Authorship Fixer

Three files:

- data.php contains the lookup tables
- bad_author_fixer.php fixes a case where the wrong WP user was assigned to some posts
- authorship_fixer.php sets or updates the largo custom byline information

To run this tool:
1. Upload the `.php` files from this repo to the server
2. Visit `/bad_author_fixer.php`.
	1. Wait until it times out.
	2. Refresh.
	3. Repeat steps 1-2 until the page loads.
3. Visit `/authorship_fixer.php`.
	1. Wait until it times out.
	2. Refresh.
	3. Repeat steps 1-2 until the page loads.
