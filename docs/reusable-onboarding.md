# Reusable Onboarding

`ivanbaric/onboarding` is the reusable setup workflow used by public-first applications.

## Package Ownership

The package owns:

- `onboarding_runs` persistence and resumable progress;
- tenant, subject and actor ownership;
- current/highest step, answers, context and lifecycle timestamps;
- processing, completion and failure states;
- the public-first Livewire wizard and completion screens;
- configured completion middleware;
- the structured Laravel AI agent, AI request action and queue job;
- the existing-Pages structure payload and safe generated-content application;
- submitted and completed domain events;
- legacy settings import and optional transition mirror.

The host application owns only:

- translated questionnaire options and product copy in `config/onboarding.php`;
- domain-specific AI instructions;
- an `InitialStructureInitializer` that deterministically creates initial Pages records;
- an optional `OnboardingAccessPolicy` for support-mode or superadmin exceptions.

## Required Flow

```text
Subject resolver
  -> OnboardingRun
  -> resumable Livewire wizard
  -> SubmitOnboardingAction
  -> deterministic structure initializer
  -> structured package AI agent
  -> ProcessOnboardingRun job
  -> safe Pages content applier
  -> OnboardingCompleted
```

AI must never create the application structure. The configured initializer creates pages and sections first; AI may only return UUIDs from the supplied payload and fill fields allowed by each section content policy.

## New Project Checklist

1. Require `ivanbaric/onboarding`, `ivanbaric/pages`, `ivanbaric/admin-ui` and `laravel/ai`.
2. Publish `onboarding.php` and run package migrations.
3. Enable package routes and choose the setup prefix and route names.
4. Configure questionnaire options, copy, appearance, provider/model and AI instructions.
5. Implement the small deterministic structure initializer.
6. Add `EnsureOnboardingIsComplete` to web and Livewire persistent middleware.
7. Configure protected paths/routes and the public subject route parameter.
8. Test direct setup access, Livewire progress persistence, queue success/failure and completion redirects.

Do not return onboarding state to a subject JSON column in new projects. `onboarding_runs` is the canonical store. The legacy mirror exists only for a staged upgrade of applications that previously used `organization.settings`.
