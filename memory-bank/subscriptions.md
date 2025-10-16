# LexCav Subscription System Implementation

This document outlines the implementation of the three-tier subscription system for lawyers and law firms on LexCav.

## Overview

The subscription system provides three tiers:

1. **Free tier** - Basic listing, the default for all lawyers and law firms
2. **Pro tier** - Priority in search results
3. **Max tier** - Top placement in search results + featured on the landing page

## Pricing Structure

### For Lawyers:
- **Free tier** - ₱0/month
- **Pro tier** - ₱1,500/month or ₱15,000/year
- **Max tier** - ₱4,000/month or ₱40,000/year

### For Law Firms:
- **Free tier** - ₱0/month
- **Pro tier** - ₱4,000/month or ₱40,000/year
- **Max tier** - ₱10,000/month or ₱100,000/year

## Implemented Components

### 1. Database Structure

The following tables have been created:

- **subscription_plans**: Stores the available subscription tiers
- **subscriptions**: Tracks user subscriptions with billing information
- **user_featured_slots**: Manages landing page rotation for Max tier subscribers

### 2. Models

The following models have been implemented:

- **SubscriptionPlan**: Defines subscription tiers and their features
- **Subscription**: Tracks individual user subscriptions
- **UserFeaturedSlot**: Manages featured slots on the landing page

Additionally, the User model has been extended with subscription-related methods:
- `subscriptions()`: Get all subscriptions for a user
- `activeSubscription()`: Get the user's current active subscription
- `hasSubscription()`: Check if a user has a specific subscription type
- `belongsToLawFirm()`: Check if a lawyer is under a law firm
- `firmSubscription()`: Get the active subscription of the lawyer's law firm

### 3. Search Prioritization

The NearbyLawyers component has been updated to:

- Join with subscriptions and subscription_plans tables
- Order results with Max tier first, Pro tier second, Free tier last
- Include subscription information in returned data

### 4. Featured Professionals on Landing Page

The following components have been added:

- **FeaturedProfessionals** Livewire component to display Max tier subscribers on the landing page
- Rotation system to cycle through Max tier subscribers
- Visual indicators for subscription tiers in the UI

### 5. Subscription Management

A subscription management system has been implemented with:

- **SubscriptionController** for handling subscription-related actions
- Subscription checkout flow for upgrading plans
- PayMongo payment gateway integration
- Account subscription management page
- Subscription inheritance for lawyers under law firms:
  - Lawyers under law firms inherit their firm's subscription
  - Subscription management is disabled for lawyers under law firms
  - Law firm's subscription details are shown to their lawyers

### 6. Feature Rotation System

A command-line tool and scheduler have been added to manage featured professionals:

- **RotateFeaturedProfessionals** command to update which Max tier subscribers are featured
- Daily rotation schedule in the Laravel task scheduler
- Fair rotation algorithm based on the day of the year

### 7. UI Implementation

The following UI components have been implemented:

- Subscription plan cards with PHP currency display
- Checkout page with PayMongo integration
- Success confirmation page
- Visual tier badges in search results
- Subscription management page in account settings
- Law firm subscription view for lawyers under law firms
- Dashboard subscription status indicators:
  - Subscription status cards on both lawyer and law firm dashboards
  - Visual indicators showing current plan with distinctive styling by tier
  - Encouragement to upgrade from free plans with highlighted benefits
  - Direct links to subscription management
  - For lawyers under firms, indication that they're using the firm's subscription

### 8. Admin Subscription Management

A comprehensive admin subscription management system has been implemented:

- **SubscriptionManagementController** with advanced filtering capabilities
- Admin view with detailed statistics and metrics:
  - Total subscription count
  - Active subscription count
  - Pro and Max tier counts
  - Monthly and annual revenue calculations
  - Plan distribution visualization
- Filtering options for subscriptions:
  - By plan type
  - By status (active, canceled, expired)
  - By user type (lawyer, law firm)
  - By date range
  - Search by name, email, etc.
- Detailed subscription views with:
  - Complete subscription details
  - Plan information
  - User profile details
  - Cancel subscription functionality

## Recent Updates

### Dashboard Subscription Status Implementation
- Added subscription status card on the lawyer dashboard showing:
  - Current subscription plan with color-coded badges (gray for Free, blue for Pro, purple for Max)
  - Billing cycle information for individual subscriptions
  - Special handling for lawyers under law firms, showing they're using their firm's subscription
  - For free plan users: prompt to upgrade with direct link to subscription management
  - For paid plan users: link to manage their subscription

### PayMongo Payment Integration Fix
- Fixed issue with PayMongo payment processing that was causing 400 Bad Request errors
- Updated the metadata format in SubscriptionController to prevent nested attributes
- Converted numeric IDs to strings in the metadata to comply with PayMongo requirements
- Confirmed that PayMongo API requires the parameter `payment_method_allowed` (not types)
- Limited payment methods to only the most reliable options:
  - Credit/Debit cards
  - GCash
- Identified JavaScript integration issues:
  - `paymongo.confirmPaymentIntent is not a function` error with GCash
  - Issues with the card processing flow
- Solution implemented:
  - Completely revamped the checkout flow to match the working invoice payment system
  - Created separate payment flows for GCash and credit cards
  - For GCash: using direct source creation and redirecting to GCash checkout
  - For credit cards: using direct API calls instead of the problematic PayMongo JS SDK
  - Implemented proper card validation with Luhn algorithm
  - Added visual formatting for card number input
  - Improved error handling for better user feedback
  - Added direct server-side processing without relying on client-side confirmations

### GCash Payment Integration Fix (May 15, 2025)
- Fixed issue with GCash payments resulting in "400 Bad Request" error with message "The value for billing.name cannot be blank"
- Root cause: The User model doesn't directly have first_name and last_name fields; these are stored in the related profile models
- Solution implemented:
  - Created helper methods in SubscriptionController and PayMongoService to properly retrieve user full names based on user type
  - For clients: Gets name from clientProfile relationship
  - For lawyers: Gets name from either lawyerProfile or lawFirmLawyer relationship depending on context
  - For law firms: Gets firm name from lawFirmProfile relationship
  - Added fallback to the user's name field if profile relations are not available
  - Implemented proper trimming of names to avoid empty space issues
- This fix ensures that the billing.name field is always populated with valid data from the appropriate profile model

### Subscription Display Improvements
- Enhanced subscription plan display on the account management pages:
  - Fixed issue where "Current Tier" was incorrectly shown for the Free plan even when on a paid plan
  - Improved visual indication of the currently active plan with distinctive styling
  - Added better labeling to clearly show which plan is active
  - Removed the ability to downgrade to the Free plan as requested
  - Fixed issue where "Upgrade to Pro" was shown even when the user already had the Pro plan
  - Added appropriate button states for all plan combinations:
    - Only show "Upgrade" buttons for plans higher than the current subscription
    - Show "Current plan is higher tier" for lower tier plans when on a higher tier
    - Replace "Downgrade to Free" with a disabled "Basic Plan" button
  - Updated the layout to more prominently display the current subscription status
  - Improved the law firm subscription view with clearer plan indication
  - Applied consistent color scheme for subscription tiers (gray for Free, blue for Pro, purple for Max)

### Nearby Lawyers View Fix (May 16, 2025)
- Fixed a critical syntax error in the nearby-lawyers.blade.php file that was preventing the page from loading
- Root cause: HTML markup error in the subscription badge display section created an unclosed tag
- The issue caused a Blade parsing error: "unexpected token 'endforeach', expecting 'elseif' or 'else' or 'endif'"
- Solution implemented:
  - Fixed the HTML structure for the Pro subscription badge that was missing closing tags
  - Corrected duplicated heading tags that were creating invalid HTML
  - Ensured proper nesting of @if/@endif Blade directives for the subscription tier display
  - Removed redundant HTML elements that were breaking the page layout
  - Maintained consistent styling for subscription tier badges (purple for Max, blue for Pro)

### Subscription Badge UI Enhancement (May 17, 2025)
- Redesigned subscription badges in the nearby-lawyers listings for better visual appeal
- Implemented consistent design patterns across all sections (individual lawyers, law firm lawyers, law firms)
- Applied requested design recommendations:
  - Created subtle badge icons that display alongside lawyer and law firm names
  - Implemented a silver/blue color scheme for Pro tier badges using a gradient from slate to blue
  - Implemented a gold/deep purple color scheme for Max tier badges using a gradient from amber to purple
  - Added subtle shadow effects to make badges stand out against white backgrounds
- Technical improvements:
  - Added helper functions at the top of the file to ensure badge design consistency
  - Refactored the code to make future badge styling changes easier to implement
  - Fixed HTML structure inconsistencies across lawyer card layouts
  - Ensured all sections (individual lawyers, law firm lawyers, and law firms) consistently show badges

### File Structure Update

Key updated files:
```
resources/views/
  subscriptions/
    checkout.blade.php (completely revamped PayMongo payment processing)
  account/
    subscription.blade.php (improved subscription plan display)
    firm-subscription.blade.php (improved firm subscription display)
  livewire/lawyers/
    nearby-lawyers.blade.php (updated subscription badge styling and display)
app/Http/Controllers/
  SubscriptionController.php (updated payment processing logic)
routes/web.php (added new route for card payment processing)
```

## File Structure

Key files in the implementation:
```
app/
  Models/
    SubscriptionPlan.php
    Subscription.php
    UserFeaturedSlot.php
  Http/Controllers/
    SubscriptionController.php
    Admin/
      SubscriptionManagementController.php
  Livewire/
    FeaturedProfessionals.php
  Console/Commands/
    RotateFeaturedProfessionals.php

database/
  migrations/
    2025_05_15_181722_create_subscription_plans_table.php
    2025_05_15_181735_create_subscriptions_table.php
    2025_05_15_181743_create_user_featured_slots_table.php
  seeders/
    SubscriptionPlanSeeder.php

resources/views/
  subscriptions/
    index.blade.php
    checkout.blade.php
    success.blade.php
  account/
    subscription.blade.php
    firm-subscription.blade.php
  admin/
    subscriptions/
      index.blade.php
      show.blade.php
  livewire/
    featured-professionals.blade.php
    lawyers/nearby-lawyers.blade.php
  lawyers/
    welcome.blade.php (includes subscription status section)
  livewire/law-firm/
    dashboard.blade.php (includes subscription status section)
```

## Usage

### For Website Visitors
- Featured lawyers/firms (Max tier subscribers) are prominently displayed on the landing page
- Search results show Pro and Max tier subscribers with badges and higher ranking

### For Lawyers/Law Firms
- Access subscription options via the account menu or "Manage Subscription" link in the dropdown
- Choose between monthly or annual billing with appropriate pricing based on account type
- Manage subscription via the Account Subscription Management page
- For lawyers under a law firm:
  - Subscription management is disabled
  - Law firm's subscription applies to them automatically
  - They can view their firm's subscription details but not modify it
- Dashboard displays current subscription status and encourages upgrades for better reach
- Free plan users are prompted to upgrade with clear benefits listed

### For Administrators
- Access all subscription data through the admin panel (Admin > Subscriptions)
- View detailed subscription statistics and revenue metrics
- Filter and search through subscriptions using various criteria
- View detailed information for individual subscriptions
- Cancel active subscriptions when needed
- Track subscription revenue for both monthly and annual billing cycles

## Payment Integration

The subscription system is integrated with PayMongo payment gateway:

- Payment intents are created and processed through PayMongo API
- Subscription records are created upon successful payment
- Support for focused payment methods (cards, GCash)

## Next Steps

Future enhancements to consider:

1. **Payment Analytics**: Enhanced dashboard for tracking subscription revenue over time
2. **Subscription Analytics**: Dashboard for lawyers to see the ROI on their subscription
3. **Custom Promotions**: Special offers like first month free or discounted annual plans
4. **Tiered Features**: More exclusive features for higher tiers (e.g., detailed analytics, verified badge)
5. **Subscription Reporting**: Generate downloadable reports for subscription data
6. **Auto-renewal reminders**: Email notifications before subscription renewal