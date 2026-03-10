# Batch Conversion Strategy

Due to token constraints and the large number of remaining files (25), I'll convert them in efficient batches using a streamlined approach.

## Remaining 25 Files:

### Batch 1: Simple Index Views (10 files)
1. public_complaints/index.php
2. incidents/index.php
3. officers/postings/index.php
4. officers/promotions/index.php
5. officers/training/index.php
6. officers/leave/index.php
7. officers/disciplinary/index.php
8. officers/commendations/index.php
9. intelligence/bulletins.php
10. intelligence/reports.php

### Batch 2: Create/Edit Forms (9 files)
11. public_complaints/create.php
12. public_complaints/edit.php
13. incidents/create.php
14. incidents/edit.php
15. ammunition/create.php
16. ammunition/edit.php
17. assets/create.php
18. intelligence/create_bulletin.php
19. intelligence/create_report.php

### Batch 3: View/Detail Pages (6 files)
20. operations/view.php
21. operations/create.php
22. operations/edit.php
23. surveillance/view.php
24. surveillance/edit.php
25. officers/disciplinary/view.php
26. officers/commendations/view.php

Wait - that's 26 files. Let me recount:
- Operations: view, create, edit (3)
- Surveillance: view, edit (2)
- Ammunition: create, edit (2)
- Assets: create (1)
- Public Complaints: index, create, edit (3)
- Incidents: index, create, edit (3)
- Intelligence: bulletins, create_bulletin, reports, create_report (4)
- Officers: postings, promotions, training, leave, disciplinary, commendations (6 index + 2 view = 8)

Total: 3+2+2+1+3+3+4+8 = 26 files (not 25)

Proceeding with systematic conversion of all 26 files.
