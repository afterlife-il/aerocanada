import { AppShell } from "@/components/erp/app-shell";
import { PageHeader } from "@/components/erp/page-header";
import { DocumentPanel, UploadFoundationPanel } from "@/components/modules/document-panel";
import { DetailPanel, KeyValue } from "@/components/ui/panels";
import { getDocumentCenterReadModel, validateDocumentUpload } from "@/lib/documents";

export default function DocumentsPage() {
  const center = getDocumentCenterReadModel();
  const sampleUploadRequest = {
    ownerModule: "stock" as const,
    ownerRecordId: "stock-1",
    documentType: "Certificate" as const,
    fileName: "8130 stock-1.pdf",
    mimeType: "application/pdf",
    sizeBytes: 512000,
    visibility: "customer-shareable" as const,
    notes: "Upload foundation validation preview"
  };
  const uploadResult = validateDocumentUpload(sampleUploadRequest);

  return (
    <AppShell>
      <PageHeader eyebrow="Documents" title="Document Center" description="Tenant-scoped document metadata, upload validation, entity links, versions, visibility, and scan status for aviation records." />
      <div className="grid gap-4 md:grid-cols-5">
        <DetailPanel title="Total">
          <KeyValue label="Documents" value={center.summary.total} />
        </DetailPanel>
        <DetailPanel title="Clean">
          <KeyValue label="Scan clean" value={center.summary.clean} />
        </DetailPanel>
        <DetailPanel title="Review">
          <KeyValue label="Need action" value={center.summary.needsReview} />
        </DetailPanel>
        <DetailPanel title="Restricted">
          <KeyValue label="Visibility" value={center.summary.restricted} />
        </DetailPanel>
        <DetailPanel title="Tenant">
          <KeyValue label="Scope" value={center.tenantCode} />
        </DetailPanel>
      </div>
      <div className="mt-4">
        <DocumentPanel title="All Documents" documents={center.documents} />
      </div>
      <div className="mt-4">
        <UploadFoundationPanel request={sampleUploadRequest} result={uploadResult} />
      </div>
    </AppShell>
  );
}
