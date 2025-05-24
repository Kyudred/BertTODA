# Database Schema - Entity Relationship Diagram

## News Entity
```
News {
    id: int(11) [PK, AUTO_INCREMENT]
    title: varchar(255) [NOT NULL]
    category: varchar(100) [NOT NULL]
    description: text [NOT NULL]
    facebook: varchar(255) [NULL]
    image: varchar(255) [NULL]
    created_at: datetime [DEFAULT CURRENT_TIMESTAMP]
}
```

## Projects Entity
```
Projects {
    id: int(11) [PK, AUTO_INCREMENT]
    title: varchar(255) [NOT NULL]
    category: varchar(50) [NOT NULL]
    description: text [NOT NULL]
    facebook: varchar(255) [NULL]
    image: varchar(255) [NULL]
    created_at: timestamp [DEFAULT CURRENT_TIMESTAMP]
}
```

## Entity Relationship
```
News 1..1 ---- 0..* Projects
```

### Notes:
- Both entities have similar structures but serve different purposes
- News is for announcements and updates
- Projects is for ongoing or completed initiatives
- Both use timestamps for tracking creation dates
- Both support optional Facebook links and images
- Both use a single description field for content 