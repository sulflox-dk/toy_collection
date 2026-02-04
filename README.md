# Toy Collection Manager

A comprehensive web application for managing and cataloging your toy collection with detailed tracking of figures, accessories, purchase history, and storage locations.

## Features

### Collection Management
- **Track individual toys** with detailed metadata (condition, price, acquisition status)
- **Component tracking** - manage figures, accessories, and parts separately
- **Purchase history** - record dates, prices, sources, and expected arrivals
- **Storage locations** - organize your collection across multiple storage areas
- **Condition tracking** - monitor condition (Mint, Near Mint, Good, Fair, Poor, Broken)
- **Authenticity verification** - flag original items vs. reproductions

### Universe & Catalog System
- **Universe organization** - Star Wars, Marvel, DC, etc.
- **Manufacturer tracking** - Hasbro, Mattel, LEGO, and more
- **Toy lines** - Organize by product lines (Vintage, Black Series, etc.)
- **Master toy database** - Reference library of all toys and their standard components

### Media Management
- **Image library** - Upload and tag photos of your collection
- **Tag system** - Organize images with custom tags
- **Image associations** - Link photos to specific toys and parts

### Dashboard & Analytics
- Collection statistics and overview widgets
- Track collection value and growth over time

## Usage

### Adding Toys to Your Collection

1. **Select a Universe** (e.g., Star Wars, Marvel)
2. **Choose Manufacturer** (e.g., Hasbro, Kenner)
3. **Pick a Toy Line** (e.g., Vintage Collection, Black Series)
4. **Select the Master Toy** - The product definition
5. **Add purchase details** - Date, price, source, storage location
6. **Track individual parts** - Add figures, accessories, weapons separately
7. **Upload photos** and tag them appropriately

### Managing Storage

Organize your collection across multiple locations:
- Shelf A, Display Case B, Storage Box #12
- Each toy and component can have its own storage location

### Tracking Acquisitions

Monitor items in various states:
- **In Hand** - Physical possession
- **Pre-order** - Reserved, awaiting release
- **Shipped** - In transit
- **Paid** - Payment sent, awaiting shipment
- **Backordered** - On manufacturer backorder
- **Customs** - Held in customs

## Technical Details

### Architecture

- **MVC Pattern** - Clean separation of concerns
- **Modular Design** - Features organized as independent modules
- **PSR-4 Autoloading** - Automatic class loading
- **Template System** - Reusable view components
- **Ajax API** - Dynamic data loading without page refreshes

### Database Design

The application uses a normalized relational database with tables for:
- Collection items (your actual toys)
- Master toy definitions (reference catalog)
- Components and parts
- Universes, manufacturers, and toy lines
- Media files and tags
- Storage locations and sources

### Key Technologies

- **Backend**: PHP 8+ with custom MVC framework
- **Database**: MySQL 8+ with utf8mb4 encoding
- **Frontend**: Bootstrap 5.3, Font Awesome, vanilla JavaScript
- **AJAX**: Fetch API for dynamic interactions