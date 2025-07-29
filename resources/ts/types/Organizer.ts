export type Organizer = {
  id: string;
  name: { [key: string]: string };
  description: string;
  status: "Live" | "Test";
  permissions: Array<{ id: string; label: string }>;
};
