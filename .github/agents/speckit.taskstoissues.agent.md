---
description: Convert existing tasks into actionable, dependency-ordered GitHub issues for the feature based on available design artifacts.
tools:
  [
    vscode/getProjectSetupInfo,
    vscode/installExtension,
    vscode/memory,
    vscode/newWorkspace,
    vscode/resolveMemoryFileUri,
    vscode/runCommand,
    vscode/vscodeAPI,
    vscode/extensions,
    vscode/askQuestions,
    execute/runNotebookCell,
    execute/testFailure,
    execute/getTerminalOutput,
    execute/awaitTerminal,
    execute/killTerminal,
    execute/runTask,
    execute/createAndRunTask,
    execute/runInTerminal,
    execute/runTests,
    read/getNotebookSummary,
    read/problems,
    read/readFile,
    read/viewImage,
    read/terminalSelection,
    read/terminalLastCommand,
    read/getTaskOutput,
    agent/runSubagent,
    edit/createDirectory,
    edit/createFile,
    edit/createJupyterNotebook,
    edit/editFiles,
    edit/editNotebook,
    edit/rename,
    search/changes,
    search/codebase,
    search/fileSearch,
    search/listDirectory,
    search/textSearch,
    search/usages,
    web/fetch,
    web/githubRepo,
    pylance-mcp-server/pylanceDocString,
    pylance-mcp-server/pylanceDocuments,
    pylance-mcp-server/pylanceFileSyntaxErrors,
    pylance-mcp-server/pylanceImports,
    pylance-mcp-server/pylanceInstalledTopLevelModules,
    pylance-mcp-server/pylanceInvokeRefactoring,
    pylance-mcp-server/pylancePythonEnvironments,
    pylance-mcp-server/pylanceRunCodeSnippet,
    pylance-mcp-server/pylanceSettings,
    pylance-mcp-server/pylanceSyntaxErrors,
    pylance-mcp-server/pylanceUpdatePythonEnvironment,
    pylance-mcp-server/pylanceWorkspaceRoots,
    pylance-mcp-server/pylanceWorkspaceUserFiles,
    browser/openBrowserPage,
    vscode.mermaid-chat-features/renderMermaidDiagram,
    ms-azuretools.vscode-containers/containerToolsConfig,
    ms-python.python/getPythonEnvironmentInfo,
    ms-python.python/getPythonExecutableCommand,
    ms-python.python/installPythonPackage,
    ms-python.python/configurePythonEnvironment,
    todo,
  ]
---

## User Input

```text
$ARGUMENTS
```

You **MUST** consider the user input before proceeding (if not empty).

## Global Speckit Rules

- Maintain style coherence with existing sibling artifacts and project conventions. New or updated specs, plans, tasks, checklists, contracts, translations, and reports must preserve established structure, naming, tone, and formatting unless the user explicitly requests a change.
- Optimize every Speckit run for minimum token usage. Reuse existing context, avoid rereading unchanged files, keep outputs terse, and avoid redundant explanations, examples, or file operations.

## Outline

1. Run `.specify/scripts/bash/check-prerequisites.sh --json --require-tasks --include-tasks` from repo root and parse FEATURE_DIR and AVAILABLE_DOCS list. All paths must be absolute. For single quotes in args like "I'm Groot", use escape syntax: e.g 'I'\''m Groot' (or double-quote if possible: "I'm Groot").
1. From the executed script, extract the path to **tasks**.
1. Get the Git remote by running:

```bash
git config --get remote.origin.url
```

> [!CAUTION]
> ONLY PROCEED TO NEXT STEPS IF THE REMOTE IS A GITHUB URL

1. For each task in the list, use the GitHub MCP server to create a new issue in the repository that is representative of the Git remote.

> [!CAUTION]
> UNDER NO CIRCUMSTANCES EVER CREATE ISSUES IN REPOSITORIES THAT DO NOT MATCH THE REMOTE URL
