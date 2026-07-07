import type { ReactNode } from "react";
import type { DocumentReadModel, DocumentUploadRequest, DocumentUploadValidationResult } from "@saas-aviation/shared";
import { DetailPanel, EmptyState, KeyValue } from "@/components/ui/panels";
import { StatusBadge } from "@/components/ui/status-badge";
import { DataTable } from "@/components/ui/data-table";

function formatBytes(value: number): string {
  if (value >= 1024 * 1024) return `${(value / (1024 * 1024)).toFixed(1)} MB`;
  return `${Math.max(1, Math.round(value / 1024))} KB`;
}

export function DocumentPanel({ title = "Documents", actions, documents }: { title?: string; actions?: ReactNode; documents: DocumentReadModel[] }) {
  return (
    <DetailPanel title={title} actions={actions}>
      {documents.length === 0 ? (
        <EmptyState title="No documents linked" detail="Documents are tenant-scoped and will appear here once linked through the shared Documents service." />
      ) : (
        <DataTable
          rows={documents}
          columns={[
            { key: "type", header: "Type", cell: (row) => row.documentType },
            { key: "title", header: "Title", cell: (row) => <span className="font-semibold">{row.title}</span> },
            { key: "file", header: "File", cell: (row) => <span className="font-mono">{row.fileName}</span> },
            { key: "status", header: "Status", cell: (row) => <StatusBadge status={row.status} /> },
            { key: "visibility", header: "Visibility", cell: (row) => row.visibility },
            { key: "size", header: "Size", cell: (row) => formatBytes(row.sizeBytes) },
            { key: "version", header: "Version", cell: (row) => `v${row.version}` },
            { key: "scan", header: "Scan", cell: (row) => row.currentVersion?.scanStatus ?? "pending" }
          ]}
        />
      )}
    </DetailPanel>
  );
}

export function UploadFoundationPanel({
  request,
  result
}: {
  request: DocumentUploadRequest;
  result: DocumentUploadValidationResult;
}) {
  return (
    <DetailPanel title="Upload Foundation">
      <div className="grid gap-4 lg:grid-cols-[1fr_1fr]">
        <div className="grid gap-3 md:grid-cols-2">
          <KeyValue label="Owner module" value={request.ownerModule} />
          <KeyValue label="Owner record" value={<span className="font-mono">{request.ownerRecordId}</span>} />
          <KeyValue label="Document type" value={request.documentType} />
          <KeyValue label="File" value={<span className="font-mono">{request.fileName}</span>} />
          <KeyValue label="MIME" value={request.mimeType} />
          <KeyValue label="Size" value={formatBytes(request.sizeBytes)} />
        </div>
        <div className="rounded-md border border-border bg-panel-muted p-3">
          <div className="text-sm font-semibold text-foreground">{result.accepted ? "Validated upload intent" : "Upload rejected"}</div>
          <div className="mt-2 text-xs text-muted">
            This foundation validates metadata, tenant context, MIME type, extension, and size. File bytes are not stored until object storage, malware scanning,
            checksum, retention, and audit persistence are approved.
          </div>
          <div className="mt-3 text-xs">
            <div className="font-semibold uppercase text-muted">Result</div>
            <div className="mt-1 font-mono text-foreground">{result.accepted ? result.intent?.securityChecks.join(", ") : result.errors.join(", ")}</div>
          </div>
        </div>
      </div>
    </DetailPanel>
  );
}
