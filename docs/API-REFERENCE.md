# API Endpoints Documentation

## Base URL
```
http://localhost:8010/index.php
```

---

## Events API

### Get Upcoming Events (Paginated)
**Endpoint**: `GET ?controller=Home&action=upcomingEvents`

**Parameters**:
- `page` (int, optional) - Page number (default: 1)
- `perPage` (int, optional) - Items per page (default: 3)

**Response**: 
```json
{
  "success": true,
  "data": [
    {
      "id_event": 1,
      "titre": "Conférence IA",
      "description": "Discussion sur les derniers progrès en IA",
      "date_event": "2025-06-15 09:00:00",
      "type": "conference",
      "lieu": "Auditorium Principal",
      "image_url": "uploads/events/..."
    }
  ],
  "pagination": {
    "page": 1,
    "perPage": 3,
    "total": 10,
    "totalPages": 4
  }
}
```

---

## Projects API

### List Projects with Filters
**Endpoint**: `GET ?controller=Project&action=filter`

**Parameters**:
- `domain` (string, optional) - Filter by domain (e.g., "IA", "Cloud")
- `status` (string, optional) - Filter by status (e.g., "en cours", "terminé")
- `supervisor` (int, optional) - Filter by supervisor/manager ID

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id_project": 1,
      "titre": "Smart City Algiers",
      "description": "...",
      "domaine": "IA",
      "statut": "en cours",
      "type_financement": "Interne",
      "membres_count": 3,
      "membres_noms": "Sarah Amrani, Yacine Kader",
      "responsable_prenom": "Mohamed",
      "responsable_nom": "Benali",
      "date_debut": "2024-01-15",
      "image_url": "uploads/projects/..."
    }
  ],
  "pagination": {
    "page": 1,
    "perPage": 10,
    "total": 15,
    "totalPages": 2
  }
}
```

### Get Project Edit Form
**Endpoint**: `GET ?controller=Project&action=edit&id=<project_id>`

**Auth**: Required (manager of project)

**Response**: HTML form page

### Update Project
**Endpoint**: `POST ?controller=Project&action=update`

**Auth**: Required (manager of project)

**Fields**:
- `id_project` (int) - Project ID
- `titre` (string) - Project title (max 255)
- `description` (text) - Project description
- `domaine` (string) - Domain/field
- `statut` (string) - Status (en cours, terminé, suspendu)
- `type_financement` (string) - Funding type
- `image_url` (string) - Project image URL

**Response**: Redirect to dashboard with success/error message

---

## Publications API

### List Publications with Filters
**Endpoint**: `GET ?controller=Publication&action=index`

**Parameters**:
- `year` (int, optional) - Filter by publication year
- `author` (int, optional) - Filter by author ID
- `type` (string, optional) - Filter by type (article, thesis, report, poster, communication)
- `domain` (string, optional) - Filter by domain (requires project link)
- `team_id` (int, optional) - Filter by team (author must be team member)
- `q` (string, optional) - Full-text search in title/abstract/DOI
- `sort` (string, optional) - Sort order (date_desc, date_asc, title_asc, title_desc)
- `page` (int, optional) - Page number

**Response**: HTML page with publications

### AJAX Filter Publications
**Endpoint**: `GET ?controller=Publication&action=filter`

**Parameters**: Same as above

**Response**:
```json
{
  "success": true,
  "data": [
    {
      "id_pub": 5,
      "titre": "Deep Learning for Smart Cities",
      "resume": "This paper explores the application of deep learning...",
      "date_publication": "2024-05-15",
      "type": "article",
      "domaine": "IA",
      "doi": "10.1234/example.doi",
      "lien_pdf": "uploads/docs/article.pdf",
      "auteurs": "Sarah Amrani, Mohamed Benali"
    }
  ],
  "pagination": {
    "page": 1,
    "perPage": 6,
    "total": 45,
    "totalPages": 8
  }
}
```

---

## Equipment API

### List Equipment
**Endpoint**: `GET ?controller=Equipment&action=index`

**Parameters**:
- `category` (string, optional) - Filter by category (Salle, Serveur, PC, Robot, etc.)
- `status` (string, optional) - Filter by status (libre, réservé, maintenance)
- `q` (string, optional) - Search by name or reference

**Response**: HTML page with equipment list

### Reserve Equipment
**Endpoint**: `POST ?controller=Equipment&action=reserve`

**Auth**: Required

**Fields**:
- `equip_id` (int) - Equipment ID
- `date_debut` (datetime) - Start datetime (format: YYYY-MM-DDTHH:mm from datetime-local input)
- `date_fin` (datetime) - End datetime
- `motif` (string, optional) - Reason for reservation

**Response**: 
- Success: Redirect to Equipment page with `?success=1`
- Error: Redirect to Equipment page with `?error=<message>`

**Possible Errors**:
- "La date de fin doit être après la date de début"
- "La réservation chevauche une réservation existante"
- "La réservation chevauche une maintenance prévue"

---

## Teams API

### Get Teams with Members (Organizational Chart)
**Endpoint**: `GET ?controller=Team&action=orgChart`

**Response**: HTML organizational chart page

### Client-Side Team Filtering
No API endpoint - filters are applied client-side in `script.js`:
- `applyTeamFilters()` function reads filter values
- Filters visible members by team and grade
- Reapplies sort order

---

## Offers API

### Get Offers Page
**Endpoint**: `GET ?controller=Offer&action=index`

**Response**: HTML offers landing page

---

## Error Handling

### AJAX Error Response
```json
{
  "success": false,
  "error": "Error message describing what went wrong",
  "data": null
}
```

### HTTP Status Codes
- `200` - Success (content returned)
- `302` - Redirect (typically after form submission)
- `401` - Unauthorized (auth required)
- `404` - Not found (invalid project/publication/equipment ID)
- `500` - Server error

---

## Rate Limiting & Caching

### Recommendations
- **Publication search**: Cache filter dropdowns for 1 hour
- **Equipment state**: Recompute on each request (due to time-based state)
- **Pagination**: No caching (user-initiated)

### Client-Side Optimization
- Debounced search input (300ms delay) to reduce AJAX calls
- Reuse rendered HTML components where possible

---

## Examples

### Example 1: Load Events Page 2
```bash
curl "http://localhost:8010/index.php?controller=Home&action=upcomingEvents&page=2&perPage=3"
```

### Example 2: Filter Projects by Supervisor
```bash
curl "http://localhost:8010/index.php?controller=Project&action=filter?supervisor=3"
```

### Example 3: Search Publications with Multiple Filters
```bash
curl "http://localhost:8010/index.php?controller=Publication&action=filter&year=2024&type=article&q=machine%20learning&page=1"
```

### Example 4: Reserve Equipment
```bash
curl -X POST "http://localhost:8010/index.php?controller=Equipment&action=reserve" \
  -d "equip_id=5&date_debut=2025-01-15T10:00&date_fin=2025-01-15T12:00&motif=Lab%20testing"
```

---

## Database Queries Used

### Publications Complex Query
```sql
SELECT 
  p.*,
  GROUP_CONCAT(u.prenom, ' ', u.nom SEPARATOR ', ') as auteurs
FROM publications p
LEFT JOIN publication_authors pa ON p.id_pub = pa.id_pub
LEFT JOIN users u ON pa.id_user = u.id_user
WHERE p.date_publication >= ? AND p.date_publication <= ?
  AND p.type = ?
  AND p.domaine = ?
GROUP BY p.id_pub
ORDER BY p.date_publication DESC
LIMIT ? OFFSET ?
```

### Equipment State Computation
```sql
SELECT 
  *,
  CASE 
    WHEN EXISTS (
      SELECT 1 FROM maintenances m 
      WHERE m.id_equip = e.id_equip 
      AND NOW() BETWEEN m.date_debut AND m.date_fin
    ) THEN 'maintenance'
    WHEN EXISTS (
      SELECT 1 FROM reservations r 
      WHERE r.id_equip = e.id_equip 
      AND NOW() BETWEEN r.date_debut AND r.date_fin
    ) THEN 'réservé'
    ELSE 'libre'
  END as etat_actuel
FROM equipment e
```

---

## Version History

- **v1.0** - December 2024
  - Initial implementation of all 8 features
  - All endpoints functional
  - Database schema verified
