export type Organizer = {
  id: string;
  name: { [key: string]: string };
  description: string;
  status: "Live" | "Test";
};
