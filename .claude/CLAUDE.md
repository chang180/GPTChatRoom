# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Development
```bash
# Start Laravel development server
php artisan serve

# Start frontend development server with hot reload
npm run dev

# Build frontend assets for production
npm run build

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### Testing
```bash
# Run all tests using Pest
php artisan test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run tests with filter
php artisan test --filter=testName
```

### Code Quality
```bash
# Format PHP code using Laravel Pint
vendor/bin/pint --dirty

# Run PHP code formatting (don't use --test flag)
vendor/bin/pint
```

### Database
```bash
# Run database migrations
php artisan migrate

# Create new migration
php artisan make:migration create_table_name

# Seed database
php artisan db:seed
```

## Architecture Overview

This is a **Laravel 12 + Vue.js 3 + Inertia.js** chat application that integrates with OpenAI's GPT-5-nano model for AI conversations.

### Key Technology Stack
- **Backend**: Laravel 12 with PHP 8.4, Laravel Jetstream (authentication), Laravel Sanctum (API auth)
- **Frontend**: Vue.js 3.3, Inertia.js 2.0, Tailwind CSS 3.4
- **Build Tool**: Vite 6.2
- **Database**: SQLite (development), MySQL (production option)
- **AI Integration**: OpenAI PHP SDK with GPT-5-nano model
- **Testing**: Pest 3.0 framework

### Core Components

#### Backend Architecture
- **Controllers**: `ChatRoomController` handles chat functionality, `HomeController` for landing page
- **Models**: `Message` (chat messages), `User` (authentication via Jetstream)
- **Services**: `GPTService` manages OpenAI API integration with both standard and streaming responses
- **Routes**: All routes defined in `routes/web.php` with authenticated middleware groups

#### Frontend Architecture
- **Pages**: `ChatRoom.vue` and `ChatRoomClient.vue` for different chat interfaces
- **Layout**: `AppLayout.vue` provides consistent navigation and structure
- **Components**: Jetstream components for authentication, custom chat components
- **State Management**: Vue 3 Composition API with reactive state

#### Database Schema
- **Messages Table**: Stores chat messages with `user_id`, `text`, `sender_type` (user/gpt/error)
- **Users Table**: Standard Jetstream user authentication

### API Integration
- **Standard Chat**: `/chat/send-message` - Regular request/response
- **Streaming Chat**: `/chat/send-message-stream` - Server-sent events for real-time streaming
- **Authentication**: All chat routes protected by Sanctum authentication middleware

### Development Conventions
- **PHP**: Uses Laravel 12 streamlined structure (no `app/Http/Kernel.php`, middleware in `bootstrap/app.php`)
- **Vue**: Composition API with `<script setup>` syntax
- **Styling**: Tailwind CSS utility classes
- **Testing**: Pest framework with feature and unit tests
- **Code Formatting**: Laravel Pint for PHP formatting

### Environment Setup
- Configure `OPENAI_API_KEY` and `OPENAI_ORGANIZATION` in `.env`
- SQLite database file: `database/database.sqlite`
- Frontend assets compiled via Vite

### Authentication Flow
- Uses Laravel Jetstream with Inertia.js for SPA authentication
- Supports registration, login, two-factor authentication, profile management
- Chat functionality requires authenticated users

### Message Flow
1. User submits message via Vue component
2. `ChatRoomController` validates and stores user message
3. `GPTService` calls OpenAI API (standard or streaming)
4. AI response stored as new message with `sender_type: 'gpt'`
5. Frontend updates chat interface with new messages

This application follows Laravel 12 conventions and uses Laravel Boost guidelines as defined in `.github/copilot-instructions.md`.