function toggle(id, t)
{
  a = {
        blind_id: id,
        c: t
  };
  api_post("blinds", a);
}