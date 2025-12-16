| Entities | Relationship & Multiplicity | Business Rule / Meaning |
| :--- | :--- | :--- |
| **Team $\to$ User** | Aggregation (**1 to Many**) | A Team is composed of many Members (Users). If the Team dissolves, the Users still exist (Aggregation). |
| **Team $\to$ User** | Association (**1 to 1**) | A Team is led by exactly **one** specific User (The Chef d'équipe). |
| **User $\to$ Project** | Association (**1 to Many**) | **The Manager Role:** A single User (Responsable) manages multiple projects. A Project has only one Manager. |
| **User $\leftrightarrow$ Project** | Association (**Many to Many**) | **The Member Role:** Many Users work on many Projects. (Implemented via `project_members` table). |
| **Project $\leftrightarrow$ Partner**| Association (**Many to Many**) | Projects can be funded or supported by multiple Partners (Companies/Universities). |
| **User $\leftrightarrow$ Publication** | Association (**Many to Many**) | A Publication has multiple Authors (Users), and a User writes multiple Publications. |
| **Project $\to$ Publication** | Association (**0..1 to Many**) | A Project produces many Publications. A Publication *can* belong to a Project, but can also exist independently (0 or 1). |
| **User $\to$ Reservation** | Association (**1 to Many**) | A User creates reservations. A reservation belongs to exactly one User. |
| **Equipment $\to$ Reservation** | Association (**1 to Many**) | Equipment can be booked many times. A reservation is for exactly one piece of Equipment. |
| **Equipment $\to$ Maintenance** | Composition (**1 to Many**) | **Strict Ownership.** Equipment has maintenance logs. If the Equipment is deleted from the DB, its maintenance history is deleted too (Composition). |
| **User $\to$ Event** | Dependency (**Many to Many**) | Users participate in or attend Events. |