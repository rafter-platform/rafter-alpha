# Rafter 🏡

Rafter is a serverless deployment platform powered by [Google Cloud](https://cloud.google.com). It leverages Google Cloud Run (and many other tools) to transform your Git repository into a fully-scalable serverless application running in the cloud - with **zero configuration**.

💰 Scales to zero when not in use, saving you money — perfect for hobby projects<br>
🔥 Automatically scales to handle load<br>
🔌 Manages, connects and creates Cloud SQL databases for your applications automatically<br>
⚡️ Connects to GitHub and supports deploy-on-push<br>
🚀 Spin up multiple environments available at vanity URLs at the click of a button<br>
✨ No Dockerfiles required

⚠️ **_Rafter is still very much a work-in-progress!_** ⚠️

## Google Cloud Services

### Cloud Run (web service)

[Official Documentation](https://cloud.google.com/products#serverless-computing)

- Creates services for each environment of each project, automatically
- Cloud Run handles all traffic roll-out, scaling, and configuration
- Environment variables are assigned to each unique service through the API

### Cloud Build (image creation)

[Official Documentation](https://cloud.google.com/cloud-build/)

- Docker images are created when code is pushed to GitHub
- Dockerfile is automatically provided based on type of project
- Currently supported: **Laravel, Node.js**

### Cloud SQL (database)

[Official Documentation](https://cloud.google.com/sql/)

- Database instances are provisioned by Rafter through the API
- Databases are created and assigned to projects automatically using the Admin API
- Environmental variables are properly assigned based on type of project

### Cloud Firestore (cache and session drivers)

**UPDATE**: This... doesn't work great, due to a number of factors. Looking into alternatives.

[Official Documentation](https://cloud.google.com/firestore)

- NoSQL database to support key-value caching and session management
- Drivers integrated automatically based on project
- No additional credentials required for consumer apps to use, since credentials are supplied within Cloud Run

### Cloud Tasks (queue driver)

[Official Documentation](https://cloud.google.com/tasks)

- Robust queue management platform
- Queues are automatically created for each environment
- Dedicated Cloud Run service is created for each project to handle incoming jobs through HTTP request payloads
- No daemon or worker instance is required
- Since Cloud Run is serverless, instances can fan out, thousands of jobs can be processed in a matter of seconds

### Cloud Storage (image artifacts and uploads)

[Official Documentation](https://cloud.google.com/storage)

- Object storage, similar to S3
- Automatically handles uploaded artifacts from Cloud Build
- Integrated into application helpers based on project type to handle user uploads

### Cloud Scheduler

[Official Documentation](https://cloud.google.com/scheduler)

- Used for firing cron events in e.g. Laravel

### Cloud Logging

[Official Documentation](https://cloud.google.com/logging)

- HTTP requests, stdout and app logs displayed inside Rafter log viewer

## Roadmap

Here are things I'd like to work on next:

- [x] Extract laravel-rafter-core into a package
- [ ] Inject Laravel Stackdriver log driver config
- Support other projects:
  - [x] Node
  - [ ] WordPress
  - [ ] Rails
  - [ ] Go
  - [ ] Custom Dockerfile
- [ ] Email driver support (does Google offer this as part of GCP?)
- [ ] Integration of Secret Manager
- [ ] Integration of commands (via PubSub)
- [ ] Integration of GCS for better uploads with Laravel
- [ ] Better Database operations
- [ ] Leverage GitHub Deployment API to mark when a branch has been deployed
- Lots of UI upgrades:
  - [x] Log viewer
  - [ ] Database information
  - [ ] User profile/settings
- Better Cloud Build optimization
  - [ ] Clone using ZIP instead of Git
  - [ ] Use secrets rather than plain text tokens for security
  - [ ] Consider adding multistage builds for Nodejs, Composer building steps
- [ ] Implement Custom Domain assignment and onboarding
- [ ] Implement an environment deletion workflow to delete resources/schedulers/etc
- [ ] Allow users to clone a public project without having to connect a source provider
- [ ] Create a CLI to allow users to push a local project without connecting a source provider

## Development notes

- Clone it
- Use [Valet](https://laravel.com/docs/6.x/valet) to run it and connect to a local MySQL database
- Run `make share` to fire up ngrok.io local tunnel
- Requires grpc PHP extension to be installed locally: `pecl install grpc`

## Inspiration

- [Laravel Vapor](https://vapor.laravel.com/) and [Laravel Forge](https://forge.laravel.com/)
- [Heroku](https://heroku.com)
